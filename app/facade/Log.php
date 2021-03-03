<?php

namespace app\facade;

/**
 * Class Log
 * @see \frame\core\log\Log
 * @mixin \frame\core\log\Log
 * @method static mixed record(string $type, $log, array $data = [], bool $now = false)
 * @method static mixed write(string $type, $log, array $data = [])
 * @method static mixed sql($log, array $data = [])
 * @method static mixed debug($log, array $data = [])
 * @method static mixed info($log, array $data = [])
 * @method static mixed notice($log, array $data = [])
 * @method static mixed warning($log, array $data = [])
 * @method static mixed error($log, array $data = [])
 * @method static bool|string getLog()
 * @method static bool clear()
 * @package app\facade
 */
class Log extends BaseFacade
{
    public static function getFacade()
    {
        return 'log';
    }
}