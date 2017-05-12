<?php
declare(strict_types=1);

namespace TreeHouse\EventSourcing\Bridge\SnapshotStore;

use TreeHouse\EventSourcing\SnapshotStrategyInterface;

final class NullSnapshotStrategy implements SnapshotStrategyInterface
{
    /**
     * @param mixed $aggregateId
     *
     * @return null
     */
    public function load($aggregateId)
    {
    }

    /**
     * @param mixed $aggregate
     *
     * @return void
     */
    public function store($aggregate)
    {
        // no op
    }

    /**
     * @param mixed $snapshot
     *
     * @return object
     */
    public function reconstituteAggregate($snapshot)
    {
    }
}
