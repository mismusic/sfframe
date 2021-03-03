<?php

namespace frame\core\console\input;

class InputOption
{
    CONST VALUE_NONE = 1;
    CONST VALUE_REQUIRED = 2;
    CONST VALUE_OPTIONAL = 4;
    CONST VALUE_IS_ARRAY = 8;

    public $name;
    public $shortName;
    public $type;
    public $description;
    public $default;

    public function __construct(string $name, int $type, $description = '', $shortName = '', $default = null)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('input option name not exists');
        }
        if (! in_array($type, [self::VALUE_NONE, self::VALUE_REQUIRED, self::VALUE_OPTIONAL,
            self::VALUE_IS_ARRAY, self::VALUE_OPTIONAL | self::VALUE_IS_ARRAY])) {
            throw new \InvalidArgumentException('input option type error');
        }
        $this->type = $type;
        if ($this->isRequired() && ! is_null($default)) {
            throw new \InvalidArgumentException('input option type is required not exists default value');
        }
        if ($this->isArray() && ! is_null($default)) {
            throw new \InvalidArgumentException('input option type is array not exists default value');
        }
        $this->name = $name;
        $this->shortName = $shortName;
        $this->description = $description;
        if ($this->isNone()) {
            $this->default = false;
        }
        else if ($this->isOptionArray()) {
            $this->default = (array) $default;
        }
        else if ($this->isArray()) {
            $this->default = [];
        }
        else {
            $this->default = $default;
        }
    }
    public function isRequired() :bool
    {
        return $this->type === ($this->type & self::VALUE_REQUIRED);
    }
    public function isArray() :bool
    {
        return $this->type === ($this->type & self::VALUE_IS_ARRAY);
    }
    public function isNone() :bool
    {
        return $this->type === ($this->type & self::VALUE_NONE);
    }
    public function isOption() :bool
    {
        return $this->type === ($this->type & self::VALUE_OPTIONAL);
    }
    public function isOptionArray() :bool
    {
        return $this->type === (self::VALUE_OPTIONAL | self::VALUE_IS_ARRAY);
    }
}