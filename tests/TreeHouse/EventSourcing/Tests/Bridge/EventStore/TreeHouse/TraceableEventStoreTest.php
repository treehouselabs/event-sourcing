<?php

namespace TreeHouse\EventSourcing\Tests\Bridge\EventStore\TreeHouse;

use PHPUnit_Framework_TestCase;
use stdClass;
use TreeHouse\Domain\EventStream;
use TreeHouse\EventSourcing\Bridge\EventStore\TreeHouse\TraceableEventStore;
use TreeHouse\EventSourcing\EventStoreInterface;
use TreeHouse\EventSourcing\VersionedEvent;

class TraceableEventStoreTest extends PHPUnit_Framework_TestCase
{
    const UUID = 'f7c9a835-0425-423a-a7db-3fb864bd015a';

    /**
     * @var TraceableEventStore
     */
    private $traceable;

    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    public function setUp()
    {
        $this->eventStore = $this->prophesize(EventStoreInterface::class);

        $this->traceable = new TraceableEventStore(
            $this->eventStore->reveal()
        );
    }

    /**
     * @test
     */
    public function it_traces_and_clears_events()
    {
        $events = new EventStream([
            $this->getEvent($version = 1, $payload = new stdClass()),
            $this->getEvent($version = 2, $payload = new stdClass()),
        ]);

        $this->traceable->trace();

        $this->traceable->append(
            $events
        );

        $this->assertEquals(
            [
                new stdClass(),
                new stdClass(),
            ],
            $this->traceable->getTracedEvents()
        );

        $this->traceable->clearEvents();

        $this->assertEquals(
            [],
            $this->traceable->getTracedEvents()
        );
    }

    /**
     * @test
     */
    public function it_appends_events()
    {
        $eventStream = new EventStream([
            $this->getEvent($version = 1),
            $this->getEvent($version = 2),
        ]);

        $this->eventStore->append(
            $eventStream
        )->shouldBeCalled();

        $this->traceable->append(
            $eventStream
        );
    }

    /**
     * @test
     */
    public function it_retrieves_event_stream()
    {
        $eventStream = new EventStream([
            $this->getEvent($version = 1),
            $this->getEvent($version = 2),
        ]);

        $this->eventStore->getStream(self::UUID)->willReturn(
            $eventStream
        );

        $this->assertEquals(
            $eventStream,
            $this->traceable->getStream(self::UUID)
        );
    }

    /**
     * @param $version
     *
     * @return VersionedEvent
     */
    private function getEvent($version, $payload = null)
    {
        if (null === $payload) {
            $payload = new stdClass();
        }

        /** @var $event VersionedEvent */
        $event = $this->prophesize(VersionedEvent::class);
        $event->getVersion()->willReturn($version);
        $event->getEvent()->willReturn($payload);

        return $event->reveal();
    }
}
