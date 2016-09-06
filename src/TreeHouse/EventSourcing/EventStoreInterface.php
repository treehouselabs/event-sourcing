<?php

namespace TreeHouse\EventSourcing;

use TreeHouse\Domain\EventStream;

interface EventStoreInterface
{
    /**
     * @param EventStream $events
     */
    public function append(EventStream $events);

    /**
     * @param $id
     *
     * @return EventStream|VersionedEvent[]
     */
    public function getStream($id);
}
