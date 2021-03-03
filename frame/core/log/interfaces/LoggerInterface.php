<?php

namespace frame\core\log\interfaces;

interface LoggerInterface
{
    public function record(string $type, $log, array $data = [], bool $now = false);
    public function write(string $type, $log, array $data = []);
    public function getLog();
    public function clear();
    public function sql($log, array $data = []);
    public function debug($log, array $data = []);
    public function info($log, array $data = []);
    public function notice($log, array $data = []);
    public function warning($log, array $data = []);
    public function error($log, array $data = []);
}