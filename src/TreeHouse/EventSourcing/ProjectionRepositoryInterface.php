<?php

namespace TreeHouse\EventSourcing;

interface ProjectionRepositoryInterface
{
    /**
     * @param string $id
     *
     * @return ProjectionInterface|null
     */
    public function load($id);

    /**
     * @param ProjectionInterface $projection
     */
    public function save(ProjectionInterface $projection);

    /**
     * @param string $id
     */
    public function remove($id);

    /**
     * Remove all projections.
     */
    public function clear();

    /**
     * @return ProjectionInterface[]
     */
    public function findAll();
}
