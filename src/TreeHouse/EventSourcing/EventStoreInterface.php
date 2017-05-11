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

    /**
     * @param $id
     * @param int $fromVersion
     * @param int|null $toVersion
     *
     * @return EventStream|VersionedEvent[]
     */
    public function getPartialStream($id, $fromVersion, $toVersion = null);
}
