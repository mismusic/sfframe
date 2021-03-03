<?php

return [
    \app\event\AfterSqlExecute::class => [
        \app\listener\RecordSqlLog::class,
        \app\listener\TestEvent::class,
    ],
];