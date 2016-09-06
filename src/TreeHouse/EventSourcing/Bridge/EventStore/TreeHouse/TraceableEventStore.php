<?php

namespace TreeHouse\EventSourcing\Bridge\EventStore\TreeHouse;

use TreeHouse\Domain\EventStream;
use TreeHouse\EventSourcing\EventStoreInterface;

class TraceableEventStore implements EventStoreInterface
{
    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * @var array
     */
    private $recorded = [];

    /**
     * @var bool
     */
    private $tracing = false;

    /**
     * @param EventStoreInterface $eventStore
     */
    public function __construct(EventStoreInterface $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * {@inheritdoc}
     */
    public function append(EventStream $events)
    {
        $this->eventStore->append($events);

        if (!$this->tracing) {
            return;
        }

        foreach ($events as $event) {
            $this->recorded[] = $event;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStream($id)
    {
        return $this->eventStore->getStream($id);
    }

    /**
     * @return array Appended events
     */
    public function getTracedEvents()
    {
        return array_map(
            function ($message) {
                return $message->getEvent();
            },
            $this->recorded
        );
    }

    /**
     * Start tracing.
     */
    public function trace()
    {
        $this->tracing = true;
    }

    /**
     * Clear any previously recorded events.
     */
    public function clearEvents()
    {
        $this->recorded = [];
    }
}
