<?php

namespace frame\core\route;

use frame\core\App;

abstract class Router
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_PATCH = 'PATCH';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_OPTIONS = 'OPTIONS';

    protected $routeGroup = [];
    protected $routeCollection = [];
    protected $group;
    protected $prefix = '';
    protected $namespace = '';
    protected $name = '';
    protected $middleware = [];
    protected $method = [];
    protected $route;
    protected $callable;

    public static function newInstance()
    {
        return App::getContainer()->make(static::class);
    }
    abstract public function addRule(string $route, $action, string $method = 'GET');
    public function prefix(string $prefix)
    {
        if (empty($prefix)) {
            return $this;
        }
        $prefix = '/' . trim($prefix, '/');
        $this->prefix = $prefix;
        return $this;
    }
    public function namespace(string $namespace)
    {
        if (empty($namespace)) {
            return $this;
        }
        $namespace = '\\' . trim($namespace, '\\');
        $this->namespace = $namespace;
        return $this;
    }
    public function name(string $name)
    {
        if (empty($name)) {
            return $this;
        }
        if (! preg_match('/^[\w\-\.]+$/i', $name, $mat)) {
            throw new \RuntimeException('route name non compliance');
        }
        $this->name = $name;
        return $this;
    }
    public function middleware($middleware)
    {
        if (empty($middleware)) {
            return $this;
        }
        if (! is_array($middleware)) {
            $middleware = [$middleware];
        }
        if (empty($middleware)) {
            $this->middleware = $middleware;
        } else {
            $this->middleware = array_unique(array_merge($this->middleware, $middleware));
        }
        return $this;
    }
}