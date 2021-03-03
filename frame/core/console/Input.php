<?php

namespace frame\core\console;

use frame\core\console\input\InputDefinition;

class Input
{
    protected $argv = [];
    protected $command = '';
    protected $inputDefinition;
    protected $arguments = [];
    protected $options = [];

    public function __construct(InputDefinition $inputDefinition)
    {
        $argv = $_SERVER['argv'] ?? $GLOBALS['argv'];
        array_shift($argv);
        if ($argv) {
            $this->command = array_shift($argv);
            $this->argv = $argv;
        }
        $this->inputDefinition = $inputDefinition;
    }
    public function setDefinitions(array $parameters)
    {
        $this->inputDefinition->setDefinitions($parameters);
        return $this;
    }
    public function getDefinitions()
    {
        return $this->inputDefinition;
    }
    public function getCommand()
    {
        return $this->command;
    }
    public function getArgument(string $name = null)
    {
        if (! is_null($name) && ! is_string($name)) {
            throw new \InvalidArgumentException('command argument type must is null or string');
        }
        $arguments = array_merge($this->inputDefinition->getArgumentValue(), $this->arguments);
        if (is_null($name)) {
            return $arguments;
        }
        if (isset($arguments[$name])) {
            return $arguments[$name];
        }
        throw new \InvalidArgumentException(sprintf('input get argument %s not exists', $name));
    }
    public function getOption(string $name = null)
    {
        if (! is_null($name) && ! is_string($name)) {
            throw new \InvalidArgumentException('command option type must is null or string');
        }
        $options = array_merge($this->inputDefinition->getOptionValue(), $this->options);
        if (is_null($name)) {
            return $options;
        }
        if (isset($options[$name])) {
            return $options[$name];
        }
        throw new \InvalidArgumentException(sprintf('input get option %s not exists', $name));
    }
    public function explainParameters()
    {
        foreach ($this->argv as $param) {
            if (strpos($param, '--') === 0 || $param[0] === '-') {  // explain option
                $this->parseOption($param);
            } else {  // explain argument
                $this->parseArgument($param);
            }
        }
        if ($this->inputDefinition->mustParamNum() > 0) {
            throw new \LogicException('input required parameters missing');
        }
    }
    protected function parseArgument($param)
    {
        $defArgument = $this->inputDefinition->getArguments();
        $argCount = count($this->arguments);
        if (isset($defArgument[$argCount])) {
            $inputArg = $defArgument[$argCount];
            $argName = $inputArg->name;
            $argVal = trim($param);
            $argVal = $inputArg->isArray() ? [$argVal] : $argVal;
        }
        else if (isset($defArgument[$argCount - 1]) && $defArgument[$argCount - 1]->isArray())
        {
            $inputArg = $defArgument[$argCount - 1];
            $argName = $inputArg->name;
            $argVal[] = trim($param);
        }
        else {
            throw new \LogicException('input argument not exists');
        }
        if ($inputArg->isRequired()) {
            $this->inputDefinition->mustParamNum --;
        }
        $this->arguments[$argName] = $argVal;
    }
    protected function parseOption($param)
    {
        if (strpos($param, '--') === 0) {  // option
            $optionStr = substr($param, 2); // remove --
            if (empty($optionStr)) {
                throw new \LogicException('input option format error');
            }
            [$optionName, $optionVal] = explode('=', $optionStr);
            if (empty($optionName)) {
                throw new \LogicException('input option name not exists');
            }
        } else {  // short option
            $optionStr = substr($param, 1);  // remove -
            if (empty($optionStr)) {
                throw new \LogicException('input option format error');
            }
            if (strpos($optionStr, '=')) {
                [$optionName, $optionVal] = explode('=', $optionStr);
                if (empty($optionName)) {
                    throw new \LogicException('input option name not exists');
                }
            } else {
                $optionName = substr($optionStr, 0, 1);
                if (strlen($optionStr) === 1) {
                    $optionVal = true;
                } else {
                    $optionVal = substr($optionStr, 1);
                }
            }
        }
        $defOptions = $this->inputDefinition->getOptions();
        $shortName = null;
        foreach ($defOptions as $defOption) {
            if ($defOption->name === $optionName || $defOption->shortName === $optionName)
            {
                $shortName = $defOption->shortName;
                if ($defOption->isRequired()) {
                    $this->inputDefinition->mustParamNum --;  // 如果该选项属于必须值，就对定义的选项必须值数量减一
                }
                if ($defOption->isArray()) {
                    if (isset($this->options[$optionName])) {
                        $optionValArr = $this->options[$optionName];
                        $optionValArr[] = $optionVal;
                        $optionVal = $optionValArr;
                    } else {
                        $optionVal = [$optionVal];
                    }
                }
                $optionName = $defOption->name;
            }
        }
        $this->options[$optionName] = $optionVal;
        if (! empty($shortName)) {
            $this->options[$shortName] = $optionVal;
        }
    }
}