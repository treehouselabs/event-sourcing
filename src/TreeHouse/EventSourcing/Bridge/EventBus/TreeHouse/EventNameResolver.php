<?php

namespace TreeHouse\EventSourcing\Bridge\EventBus\TreeHouse;

use TreeHouse\Domain\EventName;
use TreeHouse\MessageBus\MessageNameResolverInterface;

class EventNameResolver implements MessageNameResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve($message)
    {
        return (string) new EventName($message);
    }
}
