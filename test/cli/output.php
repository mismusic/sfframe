<?php

class Output {

    protected $stdout;
    public function __construct()
    {
        $this->stdout = fopen('php://stdout', 'w') ?: STDIN;
    }
    public function writeln($data)
    {
        if (is_resource($this->stdout)) {
            $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
            fwrite($this->stdout, $data);
            fflush($this->stdout);
        }
    }
}