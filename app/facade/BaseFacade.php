<?php

namespace app\facade;

use frame\core\Container;

abstract class BaseFacade
{
    abstract public static function getFacade();
    public static function createFacade()
    {
        $class = static::getFacade() ?: static::class;
        return Container::getInstance()->resolve($class);
    }
    public static function __callStatic($name, $arguments)
    {
        $facade = static::createFacade();
        return call_user_func_array([$facade, $name], $arguments);
    }
}