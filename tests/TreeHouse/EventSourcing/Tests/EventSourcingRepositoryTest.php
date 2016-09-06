<?php

namespace TreeHouse\EventSourcing\Tests;

use PHPUnit_Framework_TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TreeHouse\Domain\AggregateInterface;
use TreeHouse\Domain\EventStream;
use TreeHouse\EventSourcing\EventBusInterface;
use TreeHouse\EventSourcing\EventSourcingRepository;
use TreeHouse\EventSourcing\EventStoreInterface;
use TreeHouse\EventSourcing\VersionedEvent;

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
}
