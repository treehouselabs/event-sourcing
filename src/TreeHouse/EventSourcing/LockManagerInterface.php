<?php

namespace TreeHouse\EventSourcing;

interface LockManagerInterface
{
    /**
     * Obtain a lock.
     *
     * @param string $identifier
     *
     * @throws ConcurrencyException If lock could not be obtained for $identifier
     */
    public function obtain($identifier);

    /**
     * Check if a lock was obtained (by the current thread).
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function isObtained($identifier);

    /**
     * Check if a lock was obtained (by the any thread).
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function isLocked($identifier);

    /**
     * If locked, release it.
     *
     * @param string $identifier
     */
    public function release($identifier);

    /**
     * Release all locks.
     */
    public function releaseAll();
}
