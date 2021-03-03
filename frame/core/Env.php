<?php

namespace frame\core;

class Env
{
    protected $data = [];

    public function __construct()
    {
        $envPath = ROOT_PATH . DIRECTORY_SEPARATOR . '.env';
        $this->data = $this->parseEnvFile($envPath);
        $this->data['env'] = $this->parseSystemEnv();
    }
    public function set($name, $value = null)
    {
        if (! is_string($name) && ! is_array($name)) {
            throw new \InvalidArgumentException('env param name is invalid argument');
        }
        if (is_string($name)) {
            $this->data[$name] = $value;
        }
        if (is_array($name) && is_null($value)) {
            $this->data = array_merge($this->data, $name);
        }
    }
    public function get(string $name = null, $default = null)
    {
        if (is_null($name)) {
            return $this->data;
        }
        $names = array_filter(explode('.', $name), function ($val) {
            return $val ? true : false;
        });
        $result = '$this->data';
        foreach ($names as $name) {
            $result .= sprintf("['%s']", $name);
        }
        $result = 'return ' . $result . '?? $default;';
        $result = eval($result);
        return $result;
    }
    protected function parseEnvFile(string $filename) :array
    {
        if (! is_file($filename)) {
            return [];
        }
        $contents = file($filename, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
        if (empty($contents)) {
            return [];
        }
        $result = [];
        $identified = '';
        foreach ($contents as $content) {
            if (preg_match('#\[ (\w+) \]#ix', $content, $mat)) {
                $identified = isset($mat[1]) ? strtolower($mat[1]) : '';
            } else {
                $envItem = array_map(function ($val) {
                    return trim($val);
                }, explode('=', $content));
                if (count($envItem) !== 2) {
                    throw new \RuntimeException('env parse value num missing');
                }
                list($k, $val) = $envItem;
                $k = strtolower($k);
                $lowerVal = strtolower($val);
                if ($lowerVal === 'true') {
                    $val = true;
                } else if ($lowerVal === "{true}") {
                    $val = 'true';
                }
                if (empty($identified)) {
                    $result[$k] = $val;
                } else {
                    $result[$identified][$k] = $val;
                }
            }
        }
        return $result;
    }
    protected function parseSystemEnv() :array
    {
        $envData = getenv();
        $env = [];
        foreach ($envData as $k => $envItem) {
            $env[strtolower($k)] = $envItem;
        }
        return $env;
    }
}