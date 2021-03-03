<?php

namespace app\facade;

/**
 * Class Event
 * @see \frame\core\event\Event
 * @method static void bind(string $identified, $value)
 * @method static bool dispatch(string $identified)
 * @method static array getJob(string $identified = null)
 * @package app\event
 */
class Event extends BaseFacade
{
    public static function getFacade()
    {
        return 'event';
    }
}