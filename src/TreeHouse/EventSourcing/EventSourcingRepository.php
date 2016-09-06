<?php

namespace TreeHouse\EventSourcing;

use TreeHouse\Domain\AggregateInterface;

class EventSourcingRepository implements EventSourcingRepositoryInterface
{
    /**
     * @var EventStoreInterface
     */
    protected $eventStore;

    /**
     * @var EventBusInterface
     */
    protected $eventBus;

    /**
     * @var string
     */
    protected $aggregateClassName;

    /**
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface   $eventBus
     * @param string              $aggregateClassName
     */
    public function __construct(EventStoreInterface $eventStore, EventBusInterface $eventBus, $aggregateClassName)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->aggregateClassName = $aggregateClassName;
    }

    /**
     * @inheritdoc
     */
    public function load($id)
    {
        $stream = $this->eventStore->getStream($id);

        if (0 === count($stream)) {
            return null;
        }

        $aggregateClass = $this->aggregateClassName;

        $aggregate = $aggregateClass::createFromStream($stream);

        return $aggregate;
    }

    /**
     * @inheritdoc
     */
    public function save(AggregateInterface $aggregate)
    {
        $versionedEvents = $aggregate->getRecordedEvents();

        $this->eventStore->append($versionedEvents);

        foreach ($versionedEvents as $event) {
            $this->eventBus->handle($event);
        }

        $aggregate->clearRecordedEvents();
    }
}
