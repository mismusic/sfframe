<?php

namespace app\listener;

use app\facade\Log;
use frame\core\event\interfaces\ListenerInterface;

class RecordSqlLog implements ListenerInterface
{
    public function handle(object $event)
    {
        // record log
        $data = $event->data;
        if (config('app.debug')) {
            $log = sprintf('%s [%ss]', $data->getSql(), $data->rumTime());
            Log::sql($log);
        }
    }
}