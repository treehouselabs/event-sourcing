<?php
declare(strict_types=1);

namespace TreeHouse\EventSourcing;

interface SnapshotStrategyInterface
{
    /**
     * Returns the snapshot
     *
     * @param string $aggregateId
     *
     * @return mixed
     */
    public function load($aggregateId);

    /**
     * Store snapshot when applicable
     *
     * @param object $aggregate
     *
     * @return void
     */
    public function store($aggregate);

    /**
     * Creats the aggregate based on the snapshot.
     *
     * Gets called after the `load` method.
     *
     * @param mixed $snapshot The result of the `load` method
     *
     * @return object
     */
    public function reconstituteAggregate($snapshot);
}
