<?php

namespace TreeHouse\EventSourcing\Bridge\EventStore\TreeHouse;

use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\EventSourcing\VersionedEventFactoryInterface;
use TreeHouse\EventStore\EventInterface;

class VersionedEventFactory implements VersionedEventFactoryInterface
{
    /**
     * @param EventInterface $data
     *
     * @return VersionedEvent
     */
    public function create($data)
    {
        if (!$data instanceof EventInterface) {
            throw new \InvalidArgumentException(sprintf('Expected instance of EventInterface, got %s', gettype($data)));
        }

        return new VersionedEvent(
            $data->getId(),
            $data->getPayload(),
            $data->getName(),
            $data->getVersion(),
            $data->getDate()
        );
    }
}
