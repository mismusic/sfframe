<?php

namespace frame\core\route;

use frame\core\App;

class RouteDispatcher
{
    protected $routeCollection = [];
    protected $group;
    protected $prefix = '';
    protected $namespace = '';
    protected $name = '';

    public static function newInstance()
    {
        return App::getContainer()->make(static::class);
    }
    public function group(callable $callable)
    {
        $this->group = $callable;
        return $this;
    }
}