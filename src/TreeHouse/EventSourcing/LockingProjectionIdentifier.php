<?php

namespace TreeHouse\EventSourcing;

class LockingProjectionIdentifier
{
    const PREFIX = 'projection';

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = self::PREFIX . '-' . $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getIdentifier();
    }
}
