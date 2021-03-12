<?php

namespace app\facade;

/**
 * Class Config
 * @see \frame\core\Config
 * @mixin \frame\core\Config
 * @package app\facade
 */
class Config extends BaseFacade
{
    public static function getFacade()
    {
        return 'config';
    }
}