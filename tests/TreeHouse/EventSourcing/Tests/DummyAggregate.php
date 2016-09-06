<?php

namespace TreeHouse\EventSourcing\Tests;

use TreeHouse\Domain\AggregateInterface;
use TreeHouse\Domain\EventStreamInterface;
use TreeHouse\Domain\RecordsEventsTrait;

class DummyAggregate implements AggregateInterface
{
    use RecordsEventsTrait;

    /**
     * @inheritdoc
     */
    public static function createFromData($data)
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public static function createFromStream(EventStreamInterface $stream)
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'some-id';
    }
}
