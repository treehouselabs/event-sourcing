<?php

namespace TreeHouse\EventSourcing\Tests;

use Prophecy\Prophecy\ObjectProphecy;
use stdClass;
use TreeHouse\EventSourcing\EventSourcingRepositoryInterface;
use TreeHouse\EventSourcing\LockingEventSourcingRepository;
use TreeHouse\EventSourcing\LockManagerInterface;

class LockingEventSourcingRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LockManagerInterface|ObjectProphecy
     */
    protected $lockManager;

    /**
     * @var EventSourcingRepositoryInterface|ObjectProphecy
     */
    protected $repository;

    /**
     * @var LockingEventSourcingRepository
     */
    protected $lockingRepository;

    public function setUp()
    {
        $this->lockManager = $this->prophesize(LockManagerInterface::class);
        $this->repository = $this->prophesize(EventSourcingRepositoryInterface::class);

        $this->lockingRepository = new LockingEventSourcingRepository($this->lockManager->reveal(), $this->repository->reveal());
    }

    /**
     * @test
     */
    public function it_locks_on_load()
    {
        $expectedReturn = new stdClass();

        $this->lockManager->obtain('some_id')->shouldBeCalled();
        $this->repository->load('some_id')->willReturn($expectedReturn);

        $this->assertEquals(
            $expectedReturn,
            $this->lockingRepository->load('some_id')
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

        $this->repository->load('some_id')->willReturn($expectedReturn);

        $this->assertEquals(
            $expectedReturn,
            $this->lockingRepository->load('some_id')
        );
    }

    /**
     * @test
     */
    public function it_releases_on_save()
    {
        $aggregate = $this->prophesize(\TreeHouse\Domain\AggregateInterface::class);
        $aggregate->getId()->willReturn('some_id');
        $revealedAggregate = $aggregate->reveal();

        $this->lockManager->release('some_id')->shouldBeCalled();
        $this->repository->save($revealedAggregate)->shouldBeCalled();

        $this->lockingRepository->save($revealedAggregate);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_cannot_make_a_locking_repository_of_a_locking_repository()
    {
        new LockingEventSourcingRepository($this->lockManager->reveal(), $this->lockingRepository);
    }
}
