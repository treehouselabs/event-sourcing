<?php

namespace TreeHouse\EventSourcing;

use DateTime;
use TreeHouse\Domain\EventEnvelopeInterface;
use TreeHouse\Serialization\SerializableInterface;

class VersionedEvent implements EventEnvelopeInterface
{
    /**
     * @var string
     */
    protected $aggregateId;

    /**
     * @var object
     */
    protected $event;

    /**
     * @var string
     */
    protected $eventName;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var \DateTime
     */
    protected $dateTime;

    /**
     * @param string                $aggregateId
     * @param SerializableInterface $event
     * @param string                $eventName
     * @param int                   $version
     * @param \DateTime             $dateTime
     */
    public function __construct($aggregateId, SerializableInterface $event, $eventName, $version, DateTime $dateTime = null)
    {
        $this->aggregateId = $aggregateId;
        $this->event = $event;
        $this->eventName = $eventName;
        $this->version = $version;
        $this->dateTime = $dateTime ?: new \DateTime();
    }

    /**
     * @return string
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return SerializableInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}
