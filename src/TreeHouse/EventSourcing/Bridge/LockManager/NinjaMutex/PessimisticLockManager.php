<?php

namespace TreeHouse\EventSourcing\Bridge\LockManager\NinjaMutex;

use NinjaMutex\Lock\LockInterface;
use Psr\Log\LoggerInterface;
use TreeHouse\EventSourcing\ConcurrencyException;
use TreeHouse\EventSourcing\LockManagerInterface;

class PessimisticLockManager implements LockManagerInterface
{
    /**
     * @var LockInterface
     */
    private $lock;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array<string>
     */
    private $lockedAggregateIds = [];

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @param LockInterface $lock
     */
    public function __construct(LockInterface $lock, $prefix = '')
    {
        $this->lock = $lock;
        $this->prefix = $prefix;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function obtain($aggregateId)
    {
        $lockId = $this->prefix . $aggregateId;

        $this->logDebug(sprintf('Obtaining lock for %s', $lockId));

        if (!$this->lock->acquireLock($lockId)) {
            throw new ConcurrencyException(sprintf(
                'Could not obtain lock for aggregate with id %s',
                $aggregateId
            ));
        }
        $this->lockedAggregateIds[$aggregateId] = true;

        $this->logDebug(sprintf('Lock obtained for %s', $lockId));
    }

    /**
     * {@inheritdoc}
     */
    public function isObtained($aggregateId)
    {
        return isset($this->lockedAggregateIds[$aggregateId]);
    }

    /**
     * @param $aggregateId
     *
     * @return bool
     */
    public function isLocked($aggregateId)
    {
        $lockId = $this->prefix . $aggregateId;

        return $this->lock->isLocked($lockId);
    }

    /**
     * {@inheritdoc}
     */
    public function release($aggregateId)
    {
        if (!$this->isObtained($aggregateId)) {
            return;
        }

        $lockId = $this->prefix . $aggregateId;

        $this->lock->releaseLock($lockId);
        unset($this->lockedAggregateIds[$aggregateId]);

        $this->logDebug(sprintf('Lock released for %s', $lockId));
    }

    /**
     * Release all locks.
     */
    public function releaseAll()
    {
        foreach ($this->lockedAggregateIds as $aggregateId => $true) {
            $this->release($aggregateId);
        }
    }

    protected function logDebug($message)
    {
        if ($this->logger) {
            $this->logger->debug($message);
        }
    }
}
