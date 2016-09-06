<?php

namespace TreeHouse\EventSourcing;

use TreeHouse\Domain\AggregateInterface;

/**
 * Special event sourcing repository that tracks recorded events across multiple aggregates and which
 * can store the events in a single transaction.
 */
class LockingSharedEventSourcingRepository implements SharedEventSourcingRepositoryInterface
{
    /**
     * @var LockManagerInterface
     */
    protected $lockingManager;

    /**
     * @var SharedEventSourcingRepositoryInterface
     */
    protected $repository;

    /**
     * @var array
     */
    private $managedAggregateIds;

    /**
     * @param LockManagerInterface                   $lockingManager
     * @param SharedEventSourcingRepositoryInterface $repository
     */
    public function __construct(LockManagerInterface $lockingManager, SharedEventSourcingRepositoryInterface $repository)
    {
        if ($repository instanceof self) {
            throw new \InvalidArgumentException('Can not make a locking repository of a locking repository');
        }

        $this->lockingManager = $lockingManager;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function load($id, $aggregateClass)
    {
        $this->lockingManager->obtain($id);

        $aggregate = $this->repository->load($id, $aggregateClass);

        if (null === $aggregate) {
            $this->lockingManager->release($id);

            return null;
        }

        $this->managedAggregateIds[] = $id;

        return $aggregate;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $this->repository->save();

        foreach ($this->managedAggregateIds as $aggregateId) {
            $this->lockingManager->release(
                $aggregateId
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function manage(AggregateInterface $aggregate)
    {
        $this->lockingManager->obtain(
            $aggregate->getId()
        );

        $this->managedAggregateIds[] = $aggregate->getId();

        $this->repository->manage($aggregate);
    }
}
