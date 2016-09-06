<?php

namespace TreeHouse\EventSourcing\Tests\Bridge\EventBus\TreeHouse;

use PHPUnit_Framework_TestCase;
use TreeHouse\EventSourcing\Bridge\EventBus\TreeHouse\EventNameResolver;
use TreeHouse\EventSourcing\Tests\DummyEvent;

class EventNameResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_resolves_event_name()
    {
        $resolver = new EventNameResolver();
        $this->assertEquals('Dummy', $resolver->resolve(new DummyEvent()));
    }
}
