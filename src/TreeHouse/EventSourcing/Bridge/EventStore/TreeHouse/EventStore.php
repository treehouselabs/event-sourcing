<?php

namespace TreeHouse\EventSourcing\Bridge\EventStore\TreeHouse;

use TreeHouse\Domain\EventStream;
use TreeHouse\EventSourcing\EventStoreInterface;
use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\EventSourcing\VersionedEventFactoryInterface;
use TreeHouse\EventStore\Event;
use TreeHouse\EventStore\EventStoreInterface as TreeHouseEventStoreInterface;
use TreeHouse\EventStore\EventStream as EventStoreEventStream;
use TreeHouse\EventStore\EventStreamNotFoundException;
use TreeHouse\EventStore\Upcasting\VersionAwareInterface;

class EventStore implements EventStoreInterface
{
    /**
     * @var VersionedEventFactoryInterface
     */
    protected $versionedEventFactory;

    /**
     * @var TreeHouseEventStoreInterface
     */
    protected $eventStore;

    /**
     * @param TreeHouseEventStoreInterface   $eventStore
     * @param VersionedEventFactoryInterface $versionedEventFactory
     */
    public function __construct(TreeHouseEventStoreInterface $eventStore, VersionedEventFactoryInterface $versionedEventFactory)
    {
        $this->eventStore = $eventStore;
        $this->versionedEventFactory = $versionedEventFactory;
    }

    /**
     * @param EventStream|VersionedEvent[] $events
     */
    public function append(EventStream $events)
    {
        $eventStoreEvents = [];

        foreach ($events as $event) {
            if (!is_object($event)) {
                throw new \InvalidArgumentException(sprintf('Expects VersionedEvent, got a %s', gettype($event)));
            }
            if (!$event instanceof VersionedEvent) {
                throw new \InvalidArgumentException(sprintf('Expects VersionedEvent, got a %s', get_class($event)));
            }

            $domainEvent = $event->getEvent();

            if ($domainEvent instanceof VersionAwareInterface) {
                $payloadVersion = $domainEvent::getVersion();
            } else {
                $payloadVersion = 1;
            }

            $eventStoreEvents[] = new Event($event->getAggregateId(), $event->getEventName(), $domainEvent, $payloadVersion, $event->getVersion(), $event->getDateTime());
        }

        $stream = new EventStoreEventStream($eventStoreEvents);

        $this->eventStore->append($stream);
    }

    /**
     * @inheritdoc
     */
    public function getStream($id)
    {
        return $this->getPartialStream($id, 0);
    }

    /**
     * @inheritdoc
     */
    public function getPartialStream($id, $fromVersion, $toVersion = null)
    {
        /* @var Event[] $stream */
        try {
            $stream = $this->eventStore->getPartialStream($id, $fromVersion, $toVersion);
        } catch (EventStreamNotFoundException $e) {
            return new EventStream();
        }

        $versionedEvents = new EventStream();

        foreach ($stream as $event) {
            $versionedEvents->append(
                $this->versionedEventFactory->create($event)
            );
        }

        return $versionedEvents;
    }
}
