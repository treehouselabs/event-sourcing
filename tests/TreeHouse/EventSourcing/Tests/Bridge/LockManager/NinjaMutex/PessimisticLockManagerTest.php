<?php

namespace TreeHouse\EventSourcing\Tests\Bridge\LockManager\NinjaMutex;

use NinjaMutex\Lock\LockInterface;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use TreeHouse\EventSourcing\Bridge\LockManager\NinjaMutex\PessimisticLockManager;

class PessimisticLockManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface|ObjectProphecy
     */
    protected $logger;

    /**
     * @var LockInterface|ObjectProphecy
     */
    protected $lock;

    /**
     * @var PessimisticLockManager
     */
    protected $lockManager;

    public function setUp()
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->lock = $this->prophesize(LockInterface::class);

        $this->lockManager = new PessimisticLockManager($this->lock->reveal(), 'some-prefix-');
    }

    /**
     * @test
     */
    public function it_obtains_lock()
    {
        $this->lock->acquireLock('some-prefix-some_id')->willReturn(true);

        $this->lockManager->obtain('some_id');
    }

    /**
     * @test
     */
    public function it_logs_obtaining_lock()
    {
        $this->lockManager->setLogger($this->logger->reveal());
        $this->logger->debug(Argument::any())->shouldBeCalled();

        $this->it_obtains_lock();
    }

    /**
     * @test
     * @expectedException \TreeHouse\EventSourcing\ConcurrencyException
     */
    public function it_throws_obtaining_locked_lock()
    {
        $this->lock->acquireLock('some-prefix-some_id')->willReturn(false);

        $this->lockManager->obtain('some_id');
    }

    /**
     * @test
     */
    public function it_checks_if_lock_is_obtained()
    {
        $this->lock->acquireLock('some-prefix-some_id')->willReturn(true);

        $this->lockManager->obtain('some_id');
        $this->assertEquals(true, $this->lockManager->isObtained('some_id'));
    }

    /**
     * @test
     */
    public function it_checks_if_lock_is_not_obtained()
    {
        $this->assertEquals(false, $this->lockManager->isObtained('some_id'));
    }

    /**
     * @test
     */
    public function it_releases_lock()
    {
        $this->lock->acquireLock('some-prefix-some_id')->willReturn(true);
        $this->lockManager->obtain('some_id');

        $this->lock->releaseLock('some-prefix-some_id')->shouldBeCalled();
        $this->lockManager->release('some_id');
    }

    /**
     * @test
     */
    public function it_releases_unobtained_lock()
    {
        $this->lockManager->release('some-prefix-some_id');
        $this->lock->releaseLock('some_id')->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_logs_releasing_lock()
    {
        $this->lockManager->setLogger($this->logger->reveal());
        $this->logger->debug(Argument::any())->shouldBeCalled();

        $this->it_releases_lock();
    }

    /**
     * @test
     */
    public function it_releases_all_locks()
    {
        $ids = [1, 2, 3];

        foreach ($ids as $id) {
            $this->lock->acquireLock('some-prefix-' . $id)->willReturn(true);
            $this->lockManager->obtain($id);
        }

        $this->lock->releaseLock(Argument::any())->shouldBeCalledTimes(3);
        $this->lockManager->releaseAll();

        foreach ($ids as $id) {
            $this->assertEquals(false, $this->lockManager->isObtained($id));
        }
    }
}
