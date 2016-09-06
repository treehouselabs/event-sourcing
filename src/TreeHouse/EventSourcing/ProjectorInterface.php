<?php

namespace TreeHouse\EventSourcing;

interface ProjectorInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents();

    /**
     * @param object $event
     */
    public function handle($event);
}
