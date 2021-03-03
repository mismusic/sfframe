<?php

namespace frame\core\event\interfaces;

interface ListenerInterface
{
    public function handle(object $event);
}