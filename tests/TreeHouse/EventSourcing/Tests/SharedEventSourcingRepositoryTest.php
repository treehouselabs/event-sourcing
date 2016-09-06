<?php

namespace TreeHouse\EventSourcing\Tests;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TreeHouse\Domain\EventStream;
use TreeHouse\EventSourcing\AbstractEventSourcedAggregate;
use TreeHouse\EventSourcing\EventBusInterface;
use TreeHouse\EventSourcing\EventStoreInterface;
use TreeHouse\EventSourcing\SharedEventSourcingRepository;
use TreeHouse\EventSourcing\VersionedEvent;

class SharedEventSourcingRepositoryTest extends \PHPUnit_Framework_TestCase
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
     * @var SharedEventSourcingRepository
     */
    private $sharedRepository;

    public function setUp()
    {
        $this->eventStore = $this->prophesize(EventStoreInterface::class);
        $this->eventBus = $this->prophesize(EventBusInterface::class);

        $this->sharedRepository = new SharedEventSourcingRepository(
            $this->eventStore->reveal(),
            $this->eventBus->reveal()
        );
    }

    /**
     * @test
     */
    public function it_loads_aggregate()
    {
        $eventStream = new EventStream([new VersionedEvent('some-id', new DummyEvent(), 'Dummy', 1)]);
        $this->eventStore->getStream('some-id')->willReturn($eventStream);

        $aggregate = $this->sharedRepository->load('some-id', StubAggregate::class);

        $this->assertEquals(
            true,
            $aggregate instanceof StubAggregate
        );

        $this->assertEquals(
            true,
            $aggregate->isDummied()
        );
    }

    /**
     * @test
     */
    public function it_tries_to_load_empty_stream()
    {
        $this->eventStore->getStream('some-id')->willReturn(new EventStream());

        $aggregate = $this->sharedRepository->load('some-id', StubAggregate::class);

        $this->assertEquals(
            null,
            $aggregate
        );
    }

    /**
     * @test
     */
    public function it_saves_managed_aggregates()
    {
        $event = new VersionedEvent('some-id', new DummyEvent(), 'Dummy', 1);
        $eventStream = new EventStream([$event]);
        $this->eventStore->getStream('some-id')->willReturn($eventStream);

        $aggregate = $this->sharedRepository->load('some-id', StubAggregate::class);
        $aggregate->dummy();

        $this->eventStore->append(Argument::any())->shouldBeCalled();
        $this->eventBus->handle(Argument::any())->shouldBeCalled();

        $this->sharedRepository->save();

        $this->assertEquals(
            0,
            count($aggregate->getRecordedEvents())
        );
    }

    /**
     * @test
     */
    public function it_manages_aggregates()
    {
        $event = new VersionedEvent('some-id', new DummyEvent(), 'Dummy', 2);

        $eventStream = $this->prophesize(EventStream::class);
        $eventStream->getIterator()->willReturn(new \ArrayIterator([$event]));
        $eventStream->track(Argument::any())->shouldBeCalled();

        $aggregate = $this->prophesize(AbstractEventSourcedAggregate::class);
        $aggregate->getRecordedEvents()->willReturn($eventStream->reveal());
        $aggregate->getId()->willReturn('some-id');

        $this->sharedRepository->manage($aggregate->reveal());
    }
}
