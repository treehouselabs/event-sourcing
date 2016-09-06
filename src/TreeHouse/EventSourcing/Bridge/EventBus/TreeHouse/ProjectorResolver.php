<?php

namespace TreeHouse\EventSourcing\Bridge\EventBus\TreeHouse;

use TreeHouse\Domain\EventEnvelopeInterface;
use TreeHouse\EventSourcing\ProjectorInterface;
use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\MessageBus\MessageNameResolverInterface;
use TreeHouse\MessageBus\Middleware\Subscribers\SubscriberResolverInterface;

class ProjectorResolver implements SubscriberResolverInterface
{
    /**
     * @var callable[]
     */
    protected $mapping = [];

    /**
     * @var MessageNameResolverInterface
     */
    protected $eventNameResolver;

    /**
     * ProjectorResolver constructor.
     *
     * @param MessageNameResolverInterface $eventNameResolver
     */
    public function __construct(MessageNameResolverInterface $eventNameResolver)
    {
        $this->eventNameResolver = $eventNameResolver;
    }

    /**
     * @param object|ProjectorInterface $projector
     */
    public function registerProjector(ProjectorInterface $projector)
    {
        foreach ($projector->getSubscribedEvents() as $eventName => $method) {
            if (!isset($this->mapping[$eventName])) {
                $this->mapping[$eventName] = [];
            }

            $this->mapping[$eventName][] = [&$projector, 'handle'];
        }
    }

    /**
     * @param VersionedEvent $message
     *
     * @return callable[]
     */
    public function resolve($message)
    {
        $event = $message;

        if ($message instanceof EventEnvelopeInterface) {
            $event = $message->getEvent();
        }

        $eventName = $this->eventNameResolver->resolve($event);

        if (isset($this->mapping[$eventName])) {
            return $this->mapping[$eventName];
        }

        return [];
    }
}
