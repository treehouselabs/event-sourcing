<?php

namespace TreeHouse\EventSourcing;

interface VersionedEventFactoryInterface
{
    /**
     * @param mixed $data
     *
     * @return VersionedEvent
     */
    public function create($data);
}
