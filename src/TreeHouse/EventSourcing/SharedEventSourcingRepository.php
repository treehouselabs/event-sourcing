<?php

namespace TreeHouse\EventSourcing;

use TreeHouse\Domain\AggregateInterface;
use TreeHouse\Domain\EventStream;

/**
 * Special event sourcing repository that tracks recorded events across multiple aggregates and which
 * can store the events in a single transaction.
 */
class SharedEventSourcingRepository implements SharedEventSourcingRepositoryInterface
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
     * @var EventStream
     */
    protected $recordedEvents;

    /**
     * @var AggregateInterface[]
     */
    protected $managedAggregates = [];

    /**
     * @param EventStoreInterface $eventStore
     */
    public function __construct(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->recordedEvents = new EventStream();
    }

    /**
     * @inheritdoc
     */
    public function load($id, $aggregateClass)
    {
        $stream = $this->eventStore->getStream($id);

        if (0 === count($stream)) {
            return null;
        }

        /** @var AggregateInterface $aggregate */
        $aggregate = $aggregateClass::createFromStream($stream);

        $aggregate->getRecordedEvents()->track($this->recordedEvents);

        $this->managedAggregates[$id] = $aggregate;

        return $aggregate;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $versionedEvents = $this->recordedEvents;

        $this->eventStore->append($versionedEvents);

        foreach ($versionedEvents as $event) {
            $this->eventBus->handle($event);
        }

        $this->recordedEvents->clear();

        foreach ($this->managedAggregates as $aggregate) {
            $aggregate->clearRecordedEvents();
        }
    }

    /**
     * Brings aggregates that haven't been persisted before into a managed state, so that
     * its events are recorded in the shared stream.
     *
     * @param AggregateInterface $aggregate
     */
    public function manage(AggregateInterface $aggregate)
    {
        if (!in_array($aggregate, $this->managedAggregates)) {
            // first pull all previously recorded events
            foreach ($aggregate->getRecordedEvents() as $event) {
                $this->recordedEvents->append($event);
            }

            // now pipe, so newly recorded events are tracked
            $aggregate->getRecordedEvents()->track($this->recordedEvents);

            // mark aggregate as managed
            $this->managedAggregates[$aggregate->getId()] = $aggregate;
        } else {
            throw new \LogicException('Trying to manage an already managed aggregate');
        }
    }
}
