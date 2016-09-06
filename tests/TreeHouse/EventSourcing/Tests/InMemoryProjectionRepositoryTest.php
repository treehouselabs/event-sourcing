<?php

namespace TreeHouse\EventSourcing\Tests;

use PHPUnit_Framework_TestCase;
use TreeHouse\EventSourcing\InMemoryProjectionRepository;
use TreeHouse\EventSourcing\ProjectionInterface;

class InMemoryProjectionRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryProjectionRepository
     */
    private $repository;

    public function setUp()
    {
        $this->repository = new InMemoryProjectionRepository();
    }

    /**
     * @test
     */
    public function it_loads()
    {
        $projection = $this->prophesize(ProjectionInterface::class);
        $projection->getId()->willReturn('some-id');

        $this->repository->save($projection->reveal());

        $this->assertEquals($projection->reveal(), $this->repository->load('some-id'));
    }

    /**
     * @test
     */
    public function it_loads_unexisting()
    {
        $this->assertEquals(null, $this->repository->load('some-id'));
    }

    /**
     * @test
     */
    public function it_finds_all()
    {
        $projection = $this->prophesize(ProjectionInterface::class);
        $projection->getId()->willReturn('some-id');

        $this->repository->save($projection->reveal());

        $this->assertEquals([$projection->reveal()], $this->repository->findAll());
    }

    /**
     * @test
     */
    public function it_removes()
    {
        $projection = $this->prophesize(ProjectionInterface::class);
        $projection->getId()->willReturn('some-id');

        $this->repository->save($projection->reveal());

        $this->assertEquals([$projection->reveal()], $this->repository->findAll());

        $this->repository->remove('some-id');

        $this->assertEquals([], $this->repository->findAll());
    }
}
