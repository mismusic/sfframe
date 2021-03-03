<?php

namespace app\listener;

use app\facade\Log;
use frame\core\event\interfaces\ListenerInterface;

class TestEvent implements ListenerInterface
{
    public function handle(object $event)
    {
        // record log
        $data = $event->data;
        Log::debug('测试事件处理 ' . __CLASS__, ['runtime' => $data->rumTime()]);
    }
}