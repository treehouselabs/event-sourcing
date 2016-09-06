<?php

namespace TreeHouse\EventSourcing\Tests;

use DateTime;
use TreeHouse\Domain\EventStream;
use TreeHouse\EventSourcing\VersionedEvent;

class AbstractEventSourcedAggregateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_creates_from_stream()
    {
        $event = new VersionedEvent('some-id', new DummyEvent(), 'Dummy', 1);
        $aggregate = StubAggregate::createFromStream(new EventStream([$event]));

        $this->assertEquals(
            true,
            $aggregate->isDummied()
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_on_empty_stream()
    {
        StubAggregate::createFromStream(new EventStream());
    }

    /**
     * @test
     */
    public function it_applies_event()
    {
        $aggregate = new StubAggregate();
        $aggregate->dummy();

        $this->assertEquals(
            true,
            $aggregate->isDummied()
        );
    }

    /**
     * @test
     */
    public function it_records_events()
    {
        $aggregate = new StubAggregate();
        $aggregate->dummy();

        $this->assertEquals(
            1,
            count($aggregate->getRecordedEvents())
        );

        /** @var VersionedEvent $event */
        $event = $aggregate->getRecordedEvents()->getIterator()->current();

        $this->assertEquals(
            'some-id',
            $event->getAggregateId()
        );
        $this->assertEquals(
            new DummyEvent(),
            $event->getEvent()
        );
        $this->assertEquals(
            'Dummy',
            $event->getEventName()
        );
        $this->assertEquals(
            1,
            $event->getVersion()
        );
        $this->assertEquals(
            true,
            $event->getDateTime() instanceof DateTime
        );
    }

    /**
     * @test
     */
    public function it_clears_recorded_events()
    {
        $aggregate = new StubAggregate();
        $aggregate->dummy();

        $this->assertEquals(
            1,
            count($aggregate->getRecordedEvents())
        );

        $aggregate->clearRecordedEvents();

        $this->assertEquals(
            0,
            count($aggregate->getRecordedEvents())
        );
    }
}
