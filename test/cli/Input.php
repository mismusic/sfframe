<?php

declare(strict_types = 1);

class Input
{
    protected $defArguments = [];
    protected $defOptions = [];
    protected $arguments = [];
    protected $options = [];
    protected $args;

    CONST OPTION_VALUE_NONE = 1;
    CONST OPTION_VALUE_REQUIRED = 2;
    CONST OPTION_VALUE_OPTIONAL = 4;
    CONST OPTION_VALUE_IS_ARRAY = 8;

    CONST ARGUMENT_REQUIRED = 1;
    CONST ARGUMENT_OPTIONAL = 2;
    CONST ARGUMENT_IS_ARRAY = 4;

    public function __construct()
    {
        $argv = $_SERVER['argv'] ?? [];
        array_shift($argv);
        $this->args = $argv;
    }
    public function getOptions() :array
    {
        return $this->options ?? [];
    }
    public function getArguments() :array
    {
        return $this->arguments ?? [];
    }
    public function all() :array
    {
        return array_merge($this->arguments ?? [], $this->options ?? []);
    }
    public function addOption(string $key, int $type, $introduction, $default = null)
    {
        if ($type === self::OPTION_VALUE_REQUIRED && ! is_null($default)) {
            throw new \InvalidArgumentException('input addOption param type is required default value must is null');
        }
        $this->defOptions[$key] = [$key, $type, $introduction, $default];
        return $this;
    }
    public function addArgument(string $key, int $type, $introduction, $default = null)
    {
        $this->defArguments[] = [$key, $type, $introduction, $default];
        return $this;
    }
    public function parseParams()
    {
        foreach ($this->args as $arg) {
            if (empty($arg) || $arg === '--') {
                continue 1;
            }
            if ($arg[0] === '-' || substr($arg, 0, 2) === '--')
            {
                [$k, $val] = explode('=', $arg);
                $k = strtr($k, ['--' => '', '-' => '']);
                if (isset($this->defOptions[$k])) {
                    $optionType = $this->defOptions[$k][1];
                    if ($optionType === Input::OPTION_VALUE_NONE) {
                        $this->options[$k] = true;
                    }
                    else if (is_null($val) && ! $this->isOptional($k)) {
                        throw new \Exception(sprintf('input parseParams param %s must exists default value', $k));
                    }
                    else if ($optionType === self::OPTION_VALUE_REQUIRED)
                    {
                        $this->options[$k] = boolval($val);
                    }
                    else if ($optionType === self::OPTION_VALUE_OPTIONAL) {
                        $this->options[$k] = $val;
                    }
                    else if ($optionType === self::OPTION_VALUE_IS_ARRAY)
                    {
                        $this->options[$k][] = $val;
                    }
                    else {
                        throw new \Exception('input parseParams type error');
                    }
                } else {
                    throw new \Exception('input parseParams option value is not exists');
                }
            }
            else {
                $count = count($this->arguments);
                if (isset($this->defArguments[$count])) {
                    $argType = $this->defArguments[$count][1];
                    $this->arguments[$this->defArguments[$count][0]] = $argType === self::ARGUMENT_IS_ARRAY ? [$arg] : $arg;
                }
                else if (isset($this->defArguments[$count - 1]) && $this->defArguments[$count][1] === self::ARGUMENT_IS_ARRAY) {
                    $this->arguments[$this->defArguments[$count][0]][] = $arg;
                }
                else {
                    throw new \LogicException('input parseParams type error');
                }
            }
        }
    }
    protected function isOptional(string $key)
    {
        if (isset($this->defOptions[$key]))
        {
            [,$type] = $this->defOptions[$key];
            if ($type === self::OPTION_VALUE_OPTIONAL) {
                return true;
            } else {
                return false;
            }
        }
        else if (isset($this->defArguments[$key])) {
            [,$type] = $this->defArguments[$key];
            if ($type === self::ARGUMENT_OPTIONAL) {
                return true;
            } else {
                return false;
            }
        }
        throw new InvalidArgumentException('input isOptional key not exists');
    }
}