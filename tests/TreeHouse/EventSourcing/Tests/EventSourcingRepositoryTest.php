<?php

namespace TreeHouse\EventSourcing\Tests;

use PHPUnit_Framework_TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TreeHouse\Domain\AggregateInterface;
use TreeHouse\Domain\EventStream;
use TreeHouse\EventSourcing\Bridge\SnapshotStore\TreeHouse\BatchSnapshotStrategy;
use TreeHouse\EventSourcing\EventBusInterface;
use TreeHouse\EventSourcing\EventSourcingRepository;
use TreeHouse\EventSourcing\EventStoreInterface;
use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\EventStore\Event;
use TreeHouse\SnapshotStore\Snapshot;
use TreeHouse\SnapshotStore\SnapshotStoreInterface;

class EventSourcingRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventStoreInterface|ObjectProphecy
     */
    private $eventStore;

    /**
     * @var EventBusInterface|ObjectProphecy
     */
    private $eventBus;

    /**
     * @var EventSourcingRepository
     */
    private $repository;

    /**
     * @var SnapshotStoreInterface|ObjectProphecy
     */
    private $snapshotStore;

    public function setUp()
    {
        $this->eventStore = $this->prophesize(EventStoreInterface::class);
        $this->eventBus = $this->prophesize(EventBusInterface::class);

        $this->repository = new EventSourcingRepository($this->eventStore->reveal(), $this->eventBus->reveal(), DummyAggregate::class);
    }

    /**
     * @test
     */
    public function it_loads()
    {
        $this->eventStore->getStream('some-id')->willReturn(new EventStream(['an event']));

        $this->assertEquals(
            DummyAggregate::class,
            get_class($this->repository->load('some-id'))
        );
    }

    /**
     * @test
     */
    public function it_loads_from_snapshot_store()
    {
        $this->snapshotStore = $this->prophesize(SnapshotStoreInterface::class);

        $snapshotStrategy = new BatchSnapshotStrategy(
            $this->snapshotStore->reveal(),
            1
        );

        $this->repository = new EventSourcingRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal(),
            SnapshotableDummyAggregate::class,
            $snapshotStrategy
        );

        $this->snapshotStore->load('some-id')->willReturn(
            new Snapshot(
                'some-id',
                1,
                []
            )
        );

        $this->eventStore->getPartialStream('some-id', 1, null)->willReturn(
            new EventStream(
                [
                    new Event(
                        'some-id',
                        'some-event',
                        [],
                        1,
                        2
                    ),
                ]
            )
        );

        $aggregate = $this->repository->load('some-id');

        $this->assertEquals(
            SnapshotableDummyAggregate::class,
            get_class($aggregate)
        );

        $this->assertEquals(2, $aggregate->getVersion());
    }

    /**
     * @test
     */
    public function it_loads_unexisting_aggregate()
    {
        $this->eventStore->getStream('some-id')->willReturn(new EventStream());

        $this->assertEquals(
            null,
            $this->repository->load('some-id')
        );
    }

    /**
     * @test
     */
    public function it_saves()
    {
        $event = $this->prophesize(VersionedEvent::class)->reveal();

        $aggregate = $this->prophesize(AggregateInterface::class);
        $aggregate->getRecordedEvents()->willReturn(new EventStream([$event]));
        $aggregate->getId()->willReturn('some-id');

        // make sure it appends in eventstore
        $this->eventStore->append(new EventStream([$event]))->shouldBeCalled();
        // make sure it publishes to eventbus
        $this->eventBus->handle($event)->shouldBeCalled();

        $aggregate->clearRecordedEvents()->shouldBeCalled();

        $this->repository->save($aggregate->reveal());
    }

    /**
     * @test
     */
    public function it_saves_to_snapshot_store()
    {
        $this->snapshotStore = $this->prophesize(SnapshotStoreInterface::class);

        $snapshotStrategy = new BatchSnapshotStrategy(
            $this->snapshotStore->reveal(),
            1
        );

        $this->repository = new EventSourcingRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal(),
            SnapshotableDummyAggregate::class,
            $snapshotStrategy
        );

        $aggregate = new SnapshotableDummyAggregate();

        $this->snapshotStore->load('some-id')->willReturn(null);
        $this->snapshotStore->store($aggregate)->shouldBeCalled();

        $this->repository->save($aggregate);
    }
}
