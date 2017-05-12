<?php

namespace TreeHouse\EventSourcing\Tests;

use TreeHouse\Domain\AggregateInterface;
use TreeHouse\Domain\EventStreamInterface;
use TreeHouse\Domain\RecordsEventsTrait;
use TreeHouse\EventSourcing\VersionedEvent;
use TreeHouse\SnapshotStore\Snapshot;
use TreeHouse\SnapshotStore\SnapshotableAggregateInterface;

class SnapshotableDummyAggregate implements AggregateInterface, SnapshotableAggregateInterface
{
    use RecordsEventsTrait;

    private $version = 1;

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
    public static function createFromSnapshot(Snapshot $snapshot)
    {
        $self = new self();
        $self->version = $snapshot->getAggregateVersion();
        $self->id = $snapshot->getAggregateId();

        return $self;
    }

    /**
     * @param EventStreamInterface $stream
     *
     * @return void
     */
    public function updateFromStream(EventStreamInterface $stream)
    {
        foreach ($stream as $event) {
            $this->version = $event->getVersion();
        }
    }

    public function doDummy()
    {
        $this->recordEvent(
            new VersionedEvent(
                $this->getId(),
                new DummyEvent(),
                'Dummy',
                $this->version++
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'some-id';
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [];
    }
}
