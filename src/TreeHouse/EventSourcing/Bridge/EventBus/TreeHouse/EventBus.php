<?php

namespace TreeHouse\EventSourcing\Bridge\EventBus\TreeHouse;

use TreeHouse\EventSourcing\EventBusInterface;
use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\MessageBus\MessageBusInterface;

class EventBus implements EventBusInterface
{
    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * EventBus constructor.
     *
     * @param MessageBusInterface $messageBus
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @param VersionedEvent $event
     */
    public function handle(VersionedEvent $event)
    {
        $this->messageBus->handle($event);
    }
}
