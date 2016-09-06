<?php

namespace TreeHouse\EventSourcing\Tests;

use TreeHouse\EventSourcing\AbstractEventSourcedAggregate;

class StubAggregate extends AbstractEventSourcedAggregate
{
    private $dummied = false;

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'some-id';
    }

    public function dummy()
    {
        $this->apply(new DummyEvent());
    }

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
}
