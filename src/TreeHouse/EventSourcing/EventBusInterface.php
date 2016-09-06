<?php

namespace TreeHouse\EventSourcing;

interface EventBusInterface
{
    /**
     * @param VersionedEvent $event
     */
    public function handle(VersionedEvent $event);
}
