<?php

namespace TreeHouse\EventSourcing;

class LockingProjectionRepository implements ProjectionRepositoryInterface
{
    /**
     * @var LockManagerInterface
     */
    protected $lockManager;

    /**
     * @var ProjectionRepositoryInterface
     */
    protected $repository;

    /**
     * @param LockManagerInterface          $lockManager
     * @param ProjectionRepositoryInterface $repository
     */
    public function __construct(LockManagerInterface $lockManager, ProjectionRepositoryInterface $repository)
    {
        $this->lockManager = $lockManager;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function load($id)
    {
        $this->lockManager->obtain(
            (string) new LockingProjectionIdentifier($id)
        );

        $projection = $this->repository->load($id);

        if (null === $projection) {
            $this->lockManager->release(
                (string) new LockingProjectionIdentifier($id)
            );
        }

        return $projection;
    }

    /**
     * @param ProjectionInterface $projection
     */
    public function save(ProjectionInterface $projection)
    {
        $this->repository->save($projection);

        $this->lockManager->release(
            (string) new LockingProjectionIdentifier(
                $projection->getId()
            )
        );
    }

    /**
     * @param string $id
     */
    public function remove($id)
    {
        $this->repository->remove($id);

        $this->lockManager->release(
            (string) new LockingProjectionIdentifier($id)
        );
    }

    /**
     * Remove all projections.
     */
    public function clear()
    {
        $this->repository->clear();
    }

    /**
     * @return ProjectionInterface[]
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }
}
