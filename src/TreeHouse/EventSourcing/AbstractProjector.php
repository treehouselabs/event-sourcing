<?php

namespace TreeHouse\EventSourcing;

use InvalidArgumentException;
use TreeHouse\Domain\EventEnvelopeInterface;
use TreeHouse\Domain\EventName;

abstract class AbstractProjector implements ProjectorInterface
{
    /**
     * @inheritdoc
     */
    public function handle($event)
    {
        $eventEnvelope = null;
        $eventName = null;

        if ($event instanceof EventEnvelopeInterface) {
            $eventEnvelope = $event;

            $event = $event->getEvent();
            $eventName = $eventEnvelope->getEventName();
        }

        $map = $this->getSubscribedEvents();

        $eventName = $eventName ?: (string) new EventName($event);

        if (!isset($map[$eventName])) {
            throw new InvalidArgumentException(
                sprintf('Missing method mapping for event "%s" in class `%s`', $eventName, get_class($this))
            );
        }

        $method = $map[$eventName];

        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException(
                sprintf('Missing method "%s" in class `%s`', $method, get_class($this))
            );
        }

        $this->$method($event, $eventEnvelope);
    }
}
