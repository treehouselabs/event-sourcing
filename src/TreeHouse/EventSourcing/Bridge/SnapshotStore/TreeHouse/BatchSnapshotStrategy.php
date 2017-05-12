<?php
declare(strict_types=1);

namespace TreeHouse\EventSourcing\Bridge\SnapshotStore\TreeHouse;

use TreeHouse\EventSourcing\SnapshotStrategyInterface;
use TreeHouse\SnapshotStore\SnapshotableAggregateInterface;
use TreeHouse\SnapshotStore\SnapshotStoreInterface;

final class BatchSnapshotStrategy implements SnapshotStrategyInterface
{
    /**
     * @var SnapshotStoreInterface
     */
    private $snapshotStore;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param SnapshotStoreInterface $snapshotStore
     * @param int $batchSize
     */
    public function __construct(SnapshotStoreInterface $snapshotStore, $batchSize)
    {
        $this->snapshotStore = $snapshotStore;
        $this->batchSize = $batchSize;
    }

    /**
     * @inheritdoc
     */
    public function load($aggregateId)
    {
        return $this->snapshotStore->load($aggregateId);
    }

    /**
     * @inheritdoc
     */
    public function store($aggregate)
    {
        if (!$aggregate instanceof SnapshotableAggregateInterface) {
            return;
        }

        if (!$aggregate->getRecordedEvents()->count()) {
            return;
        }

        if ($aggregate->getVersion() % $this->batchSize !== 0) {
            return;
        }

        $this->snapshotStore->store($aggregate);
    }
}
