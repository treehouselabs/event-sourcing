<?php

namespace TreeHouse\EventSourcing\Tests\Bridge\EventBus\TreeHouse;

use TreeHouse\EventSourcing\Bridge\EventBus\TreeHouse\ProjectorResolver;
use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\MessageBus\MessageNameResolverInterface;
use TreeHouse\Serialization\SerializableArray;

class ProjectorResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->message = new VersionedEvent(
            'aaa',
            new SerializableArray(['foo' => 'bar']),
            'SerializableArray',
            1
        );
        $this->messageNameResolver = $this->prophesize(MessageNameResolverInterface::class);
        $this->messageNameResolver->resolve($this->message->getEvent())->willReturn('SerializableArray');
        $this->projector = new DummyProjector();
    }

    /**
     * @test
     */
    public function it_registers_projector()
    {
        $resolver = new ProjectorResolver($this->messageNameResolver->reveal());
        $resolver->registerProjector($this->projector);

        $this->assertEquals(
            [
                [$this->projector, 'handle'],
            ],
            $resolver->resolve($this->message)
        );

        return $resolver;
    }

    /**
     * @test
     */
    public function it_appends_projectors()
    {
        $resolver = new ProjectorResolver($this->messageNameResolver->reveal());
        $resolver->registerProjector($this->projector);

        $projector2 = new DummyProjector();

        $resolver->registerProjector($projector2);
        $resolver->registerProjector($projector2);

        $this->assertEquals(
            [
                [$this->projector, 'handle'],
                [$projector2, 'handle'],
                [$projector2, 'handle'],
            ],
            $resolver->resolve($this->message)
        );

        return $resolver;
    }

    /**
     * @test
     */
    public function it_returns_empty_array_when_subscriber_is_not_registered()
    {
        $resolver = new ProjectorResolver($this->messageNameResolver->reveal());

        $message = new VersionedEvent(
            'aaa',
            new SerializableArray(['foo' => 'bar']),
            'SerializableArray',
            1
        );

        $this->assertEquals(
            [],
            $resolver->resolve($message)
        );
    }
}
