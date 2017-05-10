<?php

namespace TreeHouse\EventSourcing;

use TreeHouse\Domain\AggregateInterface;
use TreeHouse\EventSourcing\Bridge\SnapshotStore\NullSnapshotStrategy;

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
     * @var SnapshotStrategyInterface
     */
    private $snapshotStrategy;

    /**
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface $eventBus
     * @param string $aggregateClassName
     * @param SnapshotStrategyInterface|null $snapshotStrategy
     */
    public function __construct(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus,
        $aggregateClassName,
        SnapshotStrategyInterface $snapshotStrategy = null
    ) {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->aggregateClassName = $aggregateClassName;
        $this->snapshotStrategy = $snapshotStrategy ?: new NullSnapshotStrategy();
    }

    /**
     * @inheritdoc
     */
    public function load($id)
    {
        $aggregateClass = $this->aggregateClassName;

        if ($snapshot = $this->snapshotStrategy->load($id)) {
            $aggregate = $aggregateClass::createFromSnapshot($snapshot);

            $partialStream = $this->eventStore->getPartialStream($id, $snapshot->getAggregateVersion());

            $aggregate->updateFromStream($partialStream);

            return $aggregate;
        }

        $stream = $this->eventStore->getStream($id);

        if (0 === count($stream)) {
            return null;
        }

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

        $this->snapshotStrategy->store($aggregate);

        foreach ($versionedEvents as $event) {
            $this->eventBus->handle($event);
        }

        $aggregate->clearRecordedEvents();
    }
}
