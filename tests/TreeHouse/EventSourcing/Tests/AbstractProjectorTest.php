<?php

namespace TreeHouse\EventSourcing\Tests;

use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\Serialization\SerializableInterface;

class AbstractProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_handles()
    {
        $event = new VersionedEvent(
            'some-id',
            new DummyEvent(),
            'Dummy',
            1
        );

        $projector = new StubProjector();
        $projector->handle($event);

        $this->assertEquals(
            true,
            $projector->isDummied()
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_on_missing_handler()
    {
        $event = new VersionedEvent(
            'some-id',
            $this->prophesize(SerializableInterface::class)->reveal(),
            'Class',
            1
        );

        $projector = new StubProjector();
        $projector->handle($event);
    }
}
