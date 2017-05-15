<?php
declare(strict_types=1);

namespace TreeHouse\EventSourcing\Bridge\SnapshotStore;

use TreeHouse\EventSourcing\SnapshotStrategyInterface;

final class NullSnapshotStrategy implements SnapshotStrategyInterface
{
    /**
     * @inheritdoc
     */
    public function load($aggregateId)
    {
    }

    /**
     * @inheritdoc
     */
    public function store($aggregate)
    {
        // no op
    }

    /**
     * @inheritdoc
     */
    public function reconstituteAggregate($snapshot)
    {
    }
}
