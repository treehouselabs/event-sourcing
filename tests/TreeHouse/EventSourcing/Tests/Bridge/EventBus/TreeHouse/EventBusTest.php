<?php

namespace TreeHouse\EventSourcing\Tests\Bridge\EventBus\TreeHouse;

use TreeHouse\EventSourcing\Bridge\EventBus\TreeHouse\EventBus;
use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\MessageBus\MessageBusInterface;

class EventBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_handles_event()
    {
        $event = $this->prophesize(VersionedEvent::class);
        $messageBus = $this->prophesize(MessageBusInterface::class);
        $messageBus->handle($event)->shouldBeCalled();

        $eventBus = new EventBus($messageBus->reveal());
        $eventBus->handle($event->reveal());
    }
}
