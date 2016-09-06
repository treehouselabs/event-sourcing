<?php

namespace TreeHouse\EventSourcing\Tests;

use TreeHouse\EventSourcing\AbstractProjector;

class StubProjector extends AbstractProjector
{
    protected $dummied = false;

    /**
     * @param DummyEvent $event
     */
    protected function onDummy(DummyEvent $event)
    {
        $this->dummied = true;
    }

    /**
     * @return bool
     */
    public function isDummied()
    {
        return $this->dummied;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Dummy' => 'onDummy',
        ];
    }
}
