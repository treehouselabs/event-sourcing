<?php
declare(strict_types=1);

namespace TreeHouse\EventSourcing\Tests\Bridge\SnapshotStore\TreeHouse;

use Prophecy\Argument;
use TreeHouse\EventSourcing\Bridge\SnapshotStore\TreeHouse\BatchSnapshotStrategy;
use TreeHouse\EventSourcing\Tests\SnapshotableDummyAggregate;
use TreeHouse\SnapshotStore\SnapshotStoreInterface;

final class BatchSnapshotStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_stores()
    {
        $aggregate = new SnapshotableDummyAggregate();

        $this->snapshotStore = $this->prophesize(SnapshotStoreInterface::class);

        $this->snapshotStore->store($aggregate, Argument::cetera())->shouldBeCalledTimes(2);

        $strategy = new BatchSnapshotStrategy(
            $this->snapshotStore->reveal(),
            2
        );

        $aggregate->doDummy();

        $strategy->store($aggregate); // does call

        $aggregate->doDummy();

        $strategy->store($aggregate); // does not call

        $aggregate->doDummy();

        $strategy->store($aggregate); // does call
    }
}
