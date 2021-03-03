<?php

namespace frame\core;

use app\exception\ContainerException;
use frame\utils\CoreReflection;

/**
 * 容器类
 * Class Container
 * @package frame\core
 */
class Container
{
    private static $container;

    private $classes;
    private $instances;
    private $alias;
    private $aliases = [];

    private function __construct()
    {
    }
    private function __clone()
    {
    }
    public static function getInstance()
    {
        if (is_null(self::$container)) {
            self::$container = new static();
        }
        return self::$container;
    }
    public function bind(string $abstract, $concrete = null)
    {
        // 删除以前的绑定关系
        $this->removeRelation($abstract);
        if (is_callable($concrete)) {
            $this->classes[$abstract] = $concrete;
        }
        else if (is_object($concrete)) {
            $this->instances[$abstract] = $concrete;
        }
        else if (is_null($concrete)) {
            $this->classes[$abstract] = function (App $app) use ($abstract) {
                if (class_exists($abstract)) {
                    return $app->getContainer()->resolve($abstract);
                }
                return $abstract;
            };
        } else {
            $this->classes[$abstract] = function (App $app) use ($concrete) {
                if (class_exists($concrete)) {
                    return $app->getContainer()->resolve($concrete);
                }
                return $concrete;
            };
        }
    }
    public function make(string $abstract)
    {
        return $this->resolve($abstract);
    }
    public function resolve(string $abstract, array $params = [], bool $isRecord = true)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        if (isset($this->classes[$abstract])) {
            if (is_callable($this->classes[$abstract])) {
                return $this->closure($this->classes[$abstract]);
            }
        }
        if ($aliasAbstract = $this->getAlias($abstract)) {
            return $this->resolve($aliasAbstract, $params);
        }
        try {
            $parameters = CoreReflection::getConstructor($abstract);
        } catch (\Throwable $e) {
            return null;
        }
        $args = $this->parseArgs($parameters, $params);
        $concrete = CoreReflection::newInstance($abstract, $args);
        if ($isRecord) {
            $this->bind($abstract, $concrete);
        }
        return $concrete;
    }
    public function resolveMethod(string $abstract, string $method, array $params = [])
    {
        $parameters = CoreReflection::getMethod($abstract, $method);
        $args = $this->parseArgs($parameters, $params);
        return CoreReflection::newMethod($abstract, $method, $args);
    }
    public function alias(string $alias, string $abstract)
    {
        $this->alias[$alias] = $abstract;
    }
    public function aliases(string $abstract, $alias)
    {
        if (is_string($alias)) {
            $alias = [$alias];
        }
        $aliases = $this->aliases[$abstract] ?? [];
        if (is_array($alias)) {
            $aliases = array_unique(array_values(array_merge($aliases, $alias)));
        }
        $this->aliases[$abstract] = $aliases;
    }
    public function getAlias(string $alias)
    {
        if (isset($this->alias[$alias])) {
            return $this->alias[$alias];
        }
        foreach ($this->aliases as $abstract => $aliasItem) {
            if (in_array($alias, $aliasItem)) {
                return $abstract;
            }
        }
        return false;
    }
    public function removeAlias(string $alias) :void
    {
        if (isset($this->alias[$alias])) {
            unset($this->alias[$alias]);
        }
        foreach ($this->aliases as $abstract => $aliasItem) {
            if ($k = array_search($alias, $aliasItem)) {
                unset($aliasItem[$k]);
            }
        }
    }
    public function removeRelation(string $abstract) :void
    {
        if (isset($this->classes[$abstract])) {
            unset($this->classes[$abstract]);
        }
        if (isset($this->instances[$abstract])) {
            unset($this->instances[$abstract]);
        }
    }
    private function closure($callable)
    {
        if (is_callable($callable)) {
            return $callable($this->resolve(App::class));
        }
        return $callable;
    }
    private function parseArgs(array $parameters, array $params)
    {
        $args = [];
        foreach ($parameters as $parameter) {
            if (isset($params[$parameter['name']])) {
                $args[] = $params[$parameter['name']];
            }
            else if ($parameter['is_optional']) {
                $args[] = $parameter['value'];
            }
            else {
                $typeClassName = $parameter['type'];
                if (! $typeClassName) {
                    throw new ContainerException('container resolve missing parameter');
                }
                $isInterface = CoreReflection::isInterface($typeClassName);
                if (! $isInterface && ! class_exists($typeClassName)) {
                    throw new ContainerException('container resolve missing parameter');
                }
                $parseInstance = $this->resolve($typeClassName);
                if (is_null($parseInstance)) {
                    throw new ContainerException('container resolve failed');
                }
                $args[] = $parseInstance;
                $this->bind($typeClassName, $parseInstance);
            }
        }
        return $args;
    }
}