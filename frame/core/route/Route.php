<?php

namespace frame\core\route;

class Route
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_PATCH = 'PATCH';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_OPTIONS = 'OPTIONS';

    protected $prefix = '';
    protected $namespace = '';
    protected $name = '';
    protected $route;
    protected $middleware = [];
    protected $callable;

    public static function newRoute()
    {
        return new static();
    }
    public function addRule(string $route, $action, string $method = 'get')
    {
        $method = strtoupper($method);
        if (empty($route)) {
            throw new \LogicException('add rule param route not empty');
        }
        if (! in_array($method, $this->getMethodList())) {
            throw new \LogicException('route http method not exists');
        }
        if (! is_callable($action) && ! is_array($action)) {
            throw new \InvalidArgumentException('route action type must is callable or array');
        }
        $route = '/' . trim($route, '/');
        $route = $this->prefix . $route;
        $this->route = $route;
        if (is_callable($action)) {
            $this->callable = $action;
        } else {
            [$controller, $action] = $action;
            $controller = $this->namespace . '\\' . trim($controller, '\\');
            $this->callable = [
                'controller' => $controller,
                'action' => $action,
            ];
        }
        return $this;
    }
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
        if (! preg_match('/^[\w-.]+$/i', $name, $mat)) {
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
    public function getRoute()
    {
        return get_object_vars($this);
    }
    protected function getMethodList() :array
    {
        return [
            self::HTTP_METHOD_GET,
            self::HTTP_METHOD_POST,
            self::HTTP_METHOD_PUT,
            self::HTTP_METHOD_PATCH,
            self::HTTP_METHOD_DELETE,
            self::HTTP_METHOD_OPTIONS,
        ];
    }
}