<?php

declare(strict_types = 1);

namespace frame\core\console\input;

class InputDefinition
{
    protected $arguments;
    protected $options;

    protected $argType;
    protected $optionType;
    public $mustParamNum = 0;

    public function __construct(array $parameters = [])
    {
        $this->parseParams($parameters);
    }
    public function setDefinitions(array $parameters)
    {
        $this->parseParams($parameters);
        return $this;
    }
    public function parseParams(array $parameters)
    {
        foreach ($parameters as $param) {
            if ($param instanceof InputArgument) {
                $this->parseArgument($param);
            }
            else if ($param instanceof InputOption) {
                $this->parseOption($param);
            }
            else {
                throw new \InvalidArgumentException('input definition parameters error');
            }
        }
        return $this;
    }
    public function mustParamNum()
    {
        return $this->mustParamNum;
    }
    public function getArguments() :array
    {
        return $this->arguments ?? [];
    }
    public function getOptions() :array
    {
        return $this->options ?? [];
    }
    public function getArgumentValue() :array
    {
        $arguments = $this->arguments ?? [];
        $result = [];
        foreach ($arguments as $argument) {
            $result[$argument->name] = $argument->default;
        }
        return $result;
    }
    public function getOptionValue() :array
    {
        $options = $this->options ?? [];
        $result = [];
        foreach ($options as $k => $option) {
            $result[$k] = $option->default;
        }
        return $result;
    }
    protected function parseArgument(InputArgument $inputArgument)
    {
        if ($this->argType === InputArgument::IS_ARRAY || $this->argType === (InputArgument::OPTIONAL | InputArgument::IS_ARRAY))
        {
            throw new \LogicException('definition required argument before not is array type');
        }
        if ($inputArgument->isRequired() && $this->argType && $this->argType !== $inputArgument::REQUIRED)
        {
            throw new \LogicException('definition required argument before not is other type');
        }
        if ($inputArgument->isRequired()) {
            $this->mustParamNum ++;
        }
        $this->argType = $inputArgument->type;
        $this->arguments[] = $inputArgument;
    }
    protected function parseOption(InputOption $inputOption)
    {
        if ($this->optionType === InputOption::VALUE_IS_ARRAY || $this->optionType === (InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY))
        {
            throw new \LogicException('definition required option before not is array type');
        }
        if ($inputOption->isRequired()) {
            $this->mustParamNum ++;
        }
        $this->optionType = $inputOption->type;
        $this->options[$inputOption->name] = $inputOption;
        if (! empty($inputOption->shortName)) {
            $this->options[$inputOption->shortName] = $inputOption;
        }
    }
}