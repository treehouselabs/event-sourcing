<?php

namespace TreeHouse\EventSourcing\Tests\Bridge\EventBus\TreeHouse;

use TreeHouse\EventSourcing\AbstractProjector;

class DummyProjector extends AbstractProjector
{
    /**
     * @return object[]
     */
    public static function getSubscribedEvents()
    {
        return [
            'SerializableArray' => 'onSerializableArray',
        ];
    }

    public function onSerializableTestMessage($event)
    {
        // noop
    }
}
