<?php

namespace frame\core;

class Config
{
    protected $data = [];

    public function __construct()
    {
        $this->data = $this->getConfig();
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
    protected function getConfig() :array
    {
        $configs = find_dir(App::configPath(), '.php');
        $result = [];
        foreach ($configs as $config) {
            $configRtn = [];
            if (is_file($config)) {
                $configRtn = include_once($config);
            }
            if ($configRtn) {
                $filename = basename($config, '.php');
                $result[$filename] = $configRtn;
            }
        }
        return $result;
    }
}