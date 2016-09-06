<?php

namespace TreeHouse\EventSourcing;

use InvalidArgumentException;
use TreeHouse\Domain\AggregateInterface;
use TreeHouse\Domain\EventName;
use TreeHouse\Domain\EventStreamInterface;
use TreeHouse\Domain\RecordsEventsTrait;

abstract class AbstractEventSourcedAggregate implements AggregateInterface
{
    use RecordsEventsTrait;

    protected $version = 0;

    /**
     * @inheritdoc
     */
    public static function createFromData($data)
    {
        return self::createFromStream($data);
    }

    /**
     * @inheritdoc
     */
    public static function createFromStream(EventStreamInterface $stream)
    {
        if (!count($stream)) {
            throw new InvalidArgumentException(
                'Event stream must have events before we can create the aggregate, this one is empty!'
            );
        }

        $aggregate = new static();

        /** @var VersionedEvent $event */
        foreach ($stream as $event) {
            $aggregate->mutate($event);
        }

        $aggregate->version = $event->getVersion();

        return $aggregate;
    }

    /**
     * Update in-memory state.
     *
     * @param VersionedEvent $event
     */
    private function mutate(VersionedEvent $event)
    {
        $method = 'on' . (string) new EventName($event->getEvent());

        if (method_exists($this, $method)) {
            $this->$method($event->getEvent(), $event);
        } else {
            throw new \RuntimeException(sprintf('Method %s does not exist on aggregate %s', $method, get_class($this)));
        }
    }

    /**
     * @param object $event
     */
    protected function apply($event)
    {
        $versioned = new VersionedEvent($this->getId(), $event, (string) new EventName($event), ++$this->version);

        $this->recordEvent($versioned);

        $this->mutate($versioned);
    }
}
