<?php

namespace TreeHouse\EventSourcing\Tests;

use TreeHouse\EventSourcing\AbstractEventSourcedAggregate;

class StubAggregate extends AbstractEventSourcedAggregate
{
    private $dummied = false;

    private $timesDummied = 0;

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
        $this->timesDummied++;
    }

    /**
     * @return bool
     */
    public function isDummied()
    {
        return $this->dummied;
    }

    /**
     * @return int
     */
    public function getTimesDummied()
    {
        return $this->timesDummied;
    }
}
