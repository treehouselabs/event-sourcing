<?php

namespace TreeHouse\EventSourcing\Tests\Bridge\EventStore\TreeHouse;

use Prophecy\Prophecy\ObjectProphecy;
use TreeHouse\Domain\EventStream;
use TreeHouse\EventSourcing\Bridge\EventStore\TreeHouse\EventStore;
use TreeHouse\EventSourcing\Bridge\EventStore\TreeHouse\VersionedEventFactory;
use TreeHouse\EventSourcing\Tests\DummyEvent;
use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\EventStore\Event;
use TreeHouse\EventStore\EventStoreInterface;
use TreeHouse\EventStore\EventStream as EventStoreEventStream;
use TreeHouse\EventStore\EventStreamNotFoundException;

class EventStoreTest extends \PHPUnit_Framework_TestCase
{
    const UUID = 'db85a0de-7ef5-11e5-bb58-080027960975';

    /**
     * @var EventStoreInterface|ObjectProphecy
     */
    private $treeHouseEventStore;

    /**
     * @var EventStore
     */
    private $eventStore;

    public function setUp()
    {
        $this->treeHouseEventStore = $this->prophesize(EventStoreInterface::class);
        $this->eventStore = new EventStore(
            $this->treeHouseEventStore->reveal(),
            new VersionedEventFactory()
        );
    }

    /**
     * @test
     */
    public function it_appends_events()
    {
        $events = new EventStream([
            new VersionedEvent(self::UUID, new DummyEvent(), 'Dummy', 1),
            new VersionedEvent(self::UUID, new DummyEvent(), 'Dummy', 2),
        ]);

        $this->eventStore->append($events);

        $this->treeHouseEventStore->append(new EventStoreEventStream([
            new Event(self::UUID, 'Dummy', new DummyEvent(), 1, 1),
            new Event(self::UUID, 'Dummy', new DummyEvent(), 1, 2),
        ]))->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_appends_empty()
    {
        $this->eventStore->append(new EventStream());

        $this->treeHouseEventStore->append(new EventStoreEventStream([]))->shouldHaveBeenCalled();
    }

    /**
     * @test
     * @dataProvider throwsDataProvider
     * @expectedException \InvalidArgumentException
     */
    public function it_throws($events)
    {
        $this->eventStore->append($events);
    }

    public function throwsDataProvider()
    {
        return [
            [new EventStream([new \stdClass()])],
            [new EventStream(['string'])],
        ];
    }

    /**
     * @test
     */
    public function it_gets_event_stream()
    {
        $this->treeHouseEventStore->getPartialStream(self::UUID, 0, null)->willReturn(
            new EventStoreEventStream([
                new Event(self::UUID, 'Dummy', new DummyEvent(), 1, 1),
            ])
        );

        $this->assertEquals(
            new EventStream([new VersionedEvent(self::UUID, new DummyEvent(), 'Dummy', 1)]),
            $this->eventStore->getStream(self::UUID)
        );
    }

    /**
     * @test
     */
    public function it_gets_empty_event_stream_when_event_stream_not_found_exception()
    {
        $this->treeHouseEventStore->getPartialStream(self::UUID, 0, null)->willThrow(
            new EventStreamNotFoundException(self::UUID)
        );

        $this->assertEquals(
            new EventStream(),
            $this->eventStore->getStream(self::UUID)
        );
    }
}
