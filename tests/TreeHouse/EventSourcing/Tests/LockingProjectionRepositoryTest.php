<?php

namespace TreeHouse\EventSourcing\Tests;

use Prophecy\Prophecy\ObjectProphecy;
use stdClass;
use TreeHouse\EventSourcing\LockingProjectionIdentifier;
use TreeHouse\EventSourcing\LockingProjectionRepository;
use TreeHouse\EventSourcing\LockManagerInterface;
use TreeHouse\EventSourcing\ProjectionInterface;
use TreeHouse\EventSourcing\ProjectionRepositoryInterface;

class LockingProjectionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LockManagerInterface|ObjectProphecy
     */
    protected $lockManager;

    /**
     * @var ProjectionRepositoryInterface|ObjectProphecy
     */
    protected $repository;

    /**
     * @var LockingProjectionRepository
     */
    protected $lockingRepository;

    public function setUp()
    {
        $this->lockManager = $this->prophesize(LockManagerInterface::class);
        $this->repository = $this->prophesize(ProjectionRepositoryInterface::class);

        $this->lockingRepository = new LockingProjectionRepository(
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
        $identifier = 'some_id';
        $lockingIdentifier = (string) new LockingProjectionIdentifier($identifier);

        $this->lockManager->obtain($lockingIdentifier)->shouldBeCalled();
        $this->repository->load($identifier)->willReturn($expectedReturn);

        $this->assertEquals(
            $expectedReturn,
            $this->lockingRepository->load($identifier)
        );
    }

    /**
     * @test
     */
    public function it_locks_and_releases_if_not_found()
    {
        $expectedReturn = null;
        $identifier = 'some_id';
        $lockingIdentifier = (string) new LockingProjectionIdentifier($identifier);

        $this->lockManager->obtain($lockingIdentifier)->shouldBeCalled();
        $this->lockManager->release($lockingIdentifier)->shouldBeCalled();

        $this->repository->load($identifier)->willReturn($expectedReturn);

        $this->assertEquals(
            $expectedReturn,
            $this->lockingRepository->load($identifier)
        );
    }

    /**
     * @test
     */
    public function it_releases_on_save()
    {
        $identifier = 'some_id';
        $lockingIdentifier = (string) new LockingProjectionIdentifier($identifier);

        $projection = $this->prophesize(ProjectionInterface::class);
        $projection->getId()->willReturn($identifier);
        $projection = $projection->reveal();

        $this->lockManager->release($lockingIdentifier)->shouldBeCalled();
        $this->repository->save($projection)->shouldBeCalled();

        $this->lockingRepository->save($projection);
    }
}
