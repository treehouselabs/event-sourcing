<?php
declare(strict_types=1);

namespace TreeHouse\EventSourcing;

use TreeHouse\SnapshotStore\SnapshotableAggregateInterface;

interface SnapshotStrategyInterface
{
    /**
     * @param mixed $aggregateId
     *
     * @return mixed
     */
    public function load($aggregateId);

    /**
     * Store snapshot when applicable
     *
     * @param SnapshotableAggregateInterface $aggregate
     *
     * @return void
     */
    public function store($aggregate);
}
