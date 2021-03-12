<?php

namespace frame\utils;

use app\exception\ContainerException;
use frame\core\App;

/**
 * 自定义反射类
 * Class CoreReflection
 * @package frame\utils
 */
class CoreReflection
{
    public static function getConstructor($class, bool $required = false)
    {
        if (! is_string($class) && ! is_object($class)) {
            throw new \Exception('core reflection param class type must is string or object');
        }
        $rcClass = new \ReflectionClass($class);
        $construct = $rcClass->getConstructor();
        if (is_null($construct)) {
            return [];
        }
        $parameters = $construct->getParameters();
        return self::parseParams($parameters, $required);
    }

    public static function getMethods($class)
    {
        if (! is_string($class) && ! is_object($class)) {
            throw new \Exception('core reflection param class type must is string or object');
        }
        $rcClass = new \ReflectionClass($class);
        $methods = $rcClass->getMethods();
        $result = [];
        foreach ($methods as $method) {
            $result[] = $method->getName();
        }
        return $result;
    }

    public static function getMethod($class, string $method, bool $required = false)
    {
        if (! is_string($class) && ! is_object($class)) {
            throw new \Exception('core reflection param class type must is string or object');
        }
        $rcClass = new \ReflectionClass($class);
        if (! $rcClass->hasMethod($method)) {
            throw new \Exception('core reflection class method not exists');
        }
        $method = $rcClass->getMethod($method);
        $parameters = $method->getParameters();
        return self::parseParams($parameters, $required);
    }

    public static function getFunction($function, bool $required = false)
    {
        $rcFunc = new \ReflectionFunction($function);
        $parameters = $rcFunc->getParameters();
        return self::parseParams($parameters, $required);
    }

    public static function getParameter($class, string $method, string $parameter)
    {
        $parameter = new \ReflectionParameter([$class, $method], $parameter);
        return self::getParameterInfo($parameter);
    }

    public static function isInterface($class)
    {
        if (! is_string($class) && ! is_object($class)) {
            throw new \Exception('core reflection param class type must is string or object');
        }
        return (new \ReflectionClass($class))->isInterface();
    }

    public static function implementsInterface($class, string $interface)
    {
        if (! is_string($class) && ! is_object($class)) {
            throw new \Exception('core reflection param class type must is string or object');
        }
        $rcClass = new \ReflectionClass($class);
        return $rcClass->implementsInterface($interface);
    }

    public static function isSubjectOf($class, string $parentClass)
    {
        if (! is_string($class) && ! is_object($class)) {
            throw new \Exception('core reflection param class type must is string or object');
        }
        $rcClass = new \ReflectionClass($class);
        return $rcClass->isSubclassOf($parentClass);
    }

    public static function newInstance($class, array $args = [])
    {
        if (! is_string($class) && ! is_object($class)) {
            throw new \Exception('core reflection param class type must is string or object');
        }
        return (new \ReflectionClass($class))->newInstanceArgs($args);
    }

    public static function newMethod($class, string $method, array $args = [])
    {
        if (! is_string($class) && ! is_object($class)) {
            throw new \Exception('core reflection param class type must is string or object');
        }
        if (! is_object($class)) {
            $class = App::getContainer()->resolve($class);
        }
        if (is_null($class)) {
            throw new ContainerException('container resolve failed');
        }
        return (new \ReflectionMethod($class, $method))->invokeArgs($class, $args);
    }

    public static function newFunction($function, array $args = [])
    {
        $rcFunc = new \ReflectionFunction($function);
        return $rcFunc->invokeArgs($args);
    }

    private static function parseParams(array $parameters, bool $required = false)
    {
        $result = [];
        foreach ($parameters as $parameter) {
            if ($required) {
                if (! $parameter->isOptional()) {
                    $result[] = self::getParameterInfo($parameter);
                }
            } else {
                $result[] = self::getParameterInfo($parameter);
            }
        }
        return $result;
    }
    private static function getParameterInfo(\ReflectionParameter $parameter) :array
    {
        $isOptional = $parameter->isOptional();
        $type = $parameter->hasType() ? $parameter->getType()->getName() : null;
        $name = $parameter->getName();
        return [
            'type' => $type,
            'name' => $name,
            'is_optional' => $isOptional,
            'value' => $isOptional ? $parameter->getDefaultValue() : null,
        ];
    }
}