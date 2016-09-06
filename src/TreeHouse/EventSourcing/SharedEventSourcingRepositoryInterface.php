<?php

namespace TreeHouse\EventSourcing;

use TreeHouse\Domain\AggregateInterface;

interface SharedEventSourcingRepositoryInterface
{
    /**
     * @param $id
     * @param $aggregateClass
     *
     * @return AggregateInterface|null
     */
    public function load($id, $aggregateClass);

    public function save();

    /**
     * Brings aggregates that haven't been persisted before into a managed state, so that
     * its events are recorded in the shared stream.
     *
     * @param AggregateInterface $aggregate
     */
    public function manage(AggregateInterface $aggregate);
}
