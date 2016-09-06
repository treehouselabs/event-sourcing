<?php

namespace TreeHouse\EventSourcing\Tests;

use Prophecy\Prophecy\ObjectProphecy;
use stdClass;
use TreeHouse\Domain\AggregateInterface;
use TreeHouse\EventSourcing\LockingSharedEventSourcingRepository;
use TreeHouse\EventSourcing\LockManagerInterface;
use TreeHouse\EventSourcing\SharedEventSourcingRepositoryInterface;

class SharedLockingEventSourcingRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LockManagerInterface|ObjectProphecy
     */
    protected $lockManager;

    /**
     * @var SharedEventSourcingRepositoryInterface|ObjectProphecy
     */
    protected $repository;

    /**
     * @var LockingSharedEventSourcingRepository
     */
    protected $lockingRepository;

    public function setUp()
    {
        $this->lockManager = $this->prophesize(LockManagerInterface::class);
        $this->repository = $this->prophesize(SharedEventSourcingRepositoryInterface::class);

        $this->lockingRepository = new LockingSharedEventSourcingRepository(
            $this->lockManager->reveal(),
            $this->repository->reveal()
        );
    }

    /**
     * @test
     */
    public function it_locks_on_load()
    {
        $expectedReturn = new stdClass();

        $this->lockManager->obtain('some_id')->shouldBeCalled();
        $this->repository->load('some_id', 'AggregateA')->willReturn(new stdClass());

        $this->lockManager->obtain('some_other_id')->shouldBeCalled();
        $this->repository->load('some_other_id', 'AggregateB')->willReturn(new stdClass());

        $this->assertEquals(
            $expectedReturn,
            $this->lockingRepository->load('some_id', 'AggregateA')
        );

        $this->assertEquals(
            $expectedReturn,
            $this->lockingRepository->load('some_other_id', 'AggregateB')
        );
    }

    /**
     * @test
     */
    public function it_locks_and_releases_if_not_found()
    {
        $expectedReturn = null;

        $this->lockManager->obtain('some_id')->shouldBeCalled();
        $this->lockManager->release('some_id')->shouldBeCalled();

        $this->lockManager->obtain('some_other_id')->shouldBeCalled();
        $this->lockManager->release('some_other_id')->shouldBeCalled();

        $this->repository->load('some_id', 'AggregateA')->willReturn($expectedReturn);
        $this->repository->load('some_other_id', 'AggregateB')->willReturn($expectedReturn);

        $this->assertEquals(
            $expectedReturn,
            $this->lockingRepository->load('some_id', 'AggregateA')
        );

        $this->assertEquals(
            $expectedReturn,
            $this->lockingRepository->load('some_other_id', 'AggregateB')
        );
    }

    /**
     * @test
     */
    public function it_releases_on_save()
    {
        $aggregateA = $this->prophesize(\TreeHouse\Domain\AggregateInterface::class);
        $aggregateA->getId()->willReturn('some_id');

        $aggregateB = $this->prophesize(AggregateInterface::class);
        $aggregateB->getId()->willReturn('some_other_id');

        $this->lockingRepository->manage(
            $aggregateA->reveal()
        );
        $this->lockingRepository->manage(
            $aggregateB->reveal()
        );

        $this->lockManager->release('some_id')->shouldBeCalled();
        $this->lockManager->release('some_other_id')->shouldBeCalled();

        $this->repository->save()->shouldBeCalled();

        $this->lockingRepository->save();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_cannot_make_a_locking_repository_of_a_locking_repository()
    {
        new LockingSharedEventSourcingRepository(
            $this->lockManager->reveal(),
            $this->lockingRepository
        );
    }
}
