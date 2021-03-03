<?php

namespace frame\core\console\input;

class InputArgument
{
    CONST REQUIRED = 1;
    CONST OPTIONAL = 2;
    CONST IS_ARRAY = 4;

    public $name;
    public $type;
    public $description;
    public $default;

    public function __construct(string $name, int $type, $description = '', $default = null)
    {
        $this->type = $type;
        if (empty($name)) {
            throw new \InvalidArgumentException('input argument name not exists');
        }
        if (! in_array($type, [self::REQUIRED, self::OPTIONAL, self::IS_ARRAY, self::OPTIONAL | self::IS_ARRAY])) {
            throw new \InvalidArgumentException('input argument type error');
        }
        if ($this->isRequired() && ! is_null($default)) {
            throw new \InvalidArgumentException('input argument type is required not exists default value');
        }
        if ($this->isArray() && ! is_null($default)) {
            throw new \InvalidArgumentException('input argument type is array not exists default value');
        }
        $this->name = $name;
        $this->description = $description;
        if ($this->isOptionArray()) {
            $this->default = (array) $default;
        }
        else if ($this->isArray()) {
            $this->default = [];
        }
        else {
            $this->default = $default;
        }
    }
    public function isRequired()
    {
        return $this->type === ($this->type & self::REQUIRED);
    }
    public function isOption() :bool
    {
        return $this->type === ($this->type & self::OPTIONAL);
    }
    public function isArray() :bool
    {
        return $this->type === ($this->type & self::IS_ARRAY);
    }
    public function isOptionArray() :bool
    {
        return $this->type === (self::OPTIONAL | self::IS_ARRAY);
    }
}