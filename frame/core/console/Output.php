<?php

namespace frame\core\console;

class Output
{
    protected $stdout;
    public function __construct()
    {
        $this->stdout = fopen('php://stdout', 'w') ?: STDIN;
    }
    public function writeln(... $data)
    {
        foreach ($data as $item) {
            if (is_resource($this->stdout)) {
                $item = is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item;
                fwrite($this->stdout, $item . PHP_EOL);
                fflush($this->stdout);
            }
        }
    }
}