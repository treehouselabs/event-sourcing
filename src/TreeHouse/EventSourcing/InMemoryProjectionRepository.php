<?php

namespace TreeHouse\EventSourcing;

class InMemoryProjectionRepository implements ProjectionRepositoryInterface
{
    /**
     * @var array
     */
    private $projections = [];

    /**
     * @inheritdoc
     */
    public function load($id)
    {
        if (!isset($this->projections[$id])) {
            return null;
        }

        return $this->projections[$id];
    }

    /**
     * @inheritdoc
     */
    public function save(ProjectionInterface $projection)
    {
        $this->projections[$projection->getId()] = $projection;
    }

    /**
     * @param string $id
     */
    public function remove($id)
    {
        unset($this->projections[$id]);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->projections = [];
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        return array_values($this->projections);
    }
}
