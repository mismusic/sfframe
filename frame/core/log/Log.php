<?php

namespace frame\core\log;

use app\exception\LogException;
use frame\core\App;
use frame\core\Config;
use frame\core\log\interfaces\LoggerInterface;

class Log implements LoggerInterface
{
    protected $typeList = [
        'db', 'sql', 'debug', 'info', 'notice', 'warning', 'error'
    ];
    protected $path = '';

    public function __construct(Config $config)
    {
        $filename = $config->get('app.name') ?: 'sfframe';
        $filename .= '.log';
        $path =  App::logPath();
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $this->path = $path . DIRECTORY_SEPARATOR . $filename;
    }

    public function record(string $type, $log, array $data = [], bool $now = false)
    {
        $type = strtolower(trim($type));
        if (! in_array($type, $this->typeList)) {
            throw new LogException('log record param type error');
        }
        if (! is_string($log) && ! is_numeric($log)) {
            throw new LogException('log record param log type must is string or numeric');
        }
        $fp = fopen($this->path, 'a');
        if (empty($fp)) {
            throw new LogException('log record write log failed');
        }
        $dateTime = date('Y-m-d H:i:s');
        $string = sprintf('[%s][%s] %s', $dateTime, strtoupper($type), $log);
        if ($data) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            $string = sprintf('%s %s', $string, $data);
        }
        $string .= PHP_EOL;
        $result = fwrite($fp, $string);
        if ($now) {
            fflush($fp);
        }
        fclose($fp);
        return $result;
    }

    public function write(string $type, $log, array $data = [])
    {
        $result = $this->record($type, $log, $data, true);
        return $result;
    }

    public function getLog()
    {
        $fp = fopen($this->path, 'r');
        $result = fread($fp, filesize($this->path));
        fclose($fp);
        return $result;
    }

    public function clear()
    {
        $fp = fopen($this->path, 'w');
        $result = fwrite($fp, '');
        fclose($fp);
        return $result === 0 ? true : false;
    }

    public function sql($log, array $data = [])
    {
        return $this->record('sql', $log, $data);
    }

    public function debug($log, array $data = [])
    {
        return $this->record('debug', $log, $data);
    }

    public function info($log, array $data = [])
    {
        return $this->record('info', $log, $data);
    }

    public function notice($log, array $data = [])
    {
        return $this->record('notice', $log, $data);
    }

    public function warning($log, array $data = [])
    {
        return $this->record('warning', $log, $data);
    }

    public function error($log, array $data = [])
    {
        return $this->record('error', $log, $data);
    }

    public function getPath() :string
    {
        return $this->path;
    }
}