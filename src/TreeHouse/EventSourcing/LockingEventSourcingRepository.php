<?php

namespace TreeHouse\EventSourcing;

use TreeHouse\Domain\AggregateInterface;

class LockingEventSourcingRepository implements EventSourcingRepositoryInterface
{
    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var EventSourcingRepositoryInterface
     */
    private $repository;

    /**
     * @param LockManagerInterface             $lockManager
     * @param EventSourcingRepositoryInterface $repository
     */
    public function __construct(LockManagerInterface $lockManager, EventSourcingRepositoryInterface $repository)
    {
        if ($repository instanceof self) {
            throw new \InvalidArgumentException('Can not make a locking repository of a locking repository');
        }

        $this->lockManager = $lockManager;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function load($id)
    {
        $this->lockManager->obtain($id);

        $aggregate = $this->repository->load($id);

        if (null === $aggregate) {
            $this->lockManager->release($id);
        }

        return $aggregate;
    }

    /**
     * @inheritdoc
     */
    public function save(AggregateInterface $aggregate)
    {
        $result = $this->repository->save($aggregate);

        $this->lockManager->release(
            $aggregate->getId()
        );

        return $result;
    }
}
