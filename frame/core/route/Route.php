<?php

namespace frame\core\route;

class Route extends Router
{
    protected $matches = [];
    protected $variables = [];

    public function addRule(string $route, $action, $method = 'GET')
    {
        if (empty($route)) {
            throw new \LogicException('add rule param route not empty');
        }
        if (! is_string($method) && ! is_array($method)) {
            throw new \InvalidArgumentException('add rule param method type must is string or array');
        }
        if (! is_callable($action, true)) {
            throw new \InvalidArgumentException('route action type must is callable');
        }
        if (is_array($method)) {
            foreach ($method as & $methodItem) {
                $methodItem = strtoupper($methodItem);
                if (! in_array($methodItem, $this->getMethodList())) {
                    throw new \LogicException('route http method type not allow');
                }
            }
        } else {
            $method = strtoupper($method);
            if (! in_array($method, $this->getMethodList()) && ! $method = '*') {
                throw new \LogicException('route http method not exists');
            }
            $method = $method === '*' ? $this->getMethodList() : [$method];
        }
        $this->method = $method;
        $route = '/' . trim($route, '/');
        $this->route = $route;
        if (preg_match_all('#< ( [a-zA-Z][\w]*\?? ) >(?>/?)#ix', $route, $mat))
        {
            if (! empty($mat)) {
                 foreach ($mat[1] as $matItem) {
                     $isOptional = false;
                     if (substr($matItem, -1, 1) === '?') {
                         $matItem = substr($matItem, 0, -1);
                         $isOptional = true;
                     }
                     $this->variables[$matItem] = [
                         'name' => $matItem,
                         'is_optional' => $isOptional,
                     ];
                 }
            }
        }
        if (is_callable($action, true)) {
            if (is_array($action)) {
                [$controller, $action] = $action;
                $controller = trim($controller, '\\');
                $this->callable = [
                    'controller' => $controller,
                    'action' => $action,
                ];
            } else {
                $this->callable = $action;
            }
        }
        return $this;
    }
    public function match($name, string $value = null)
    {
        if (is_string($name)) {
            $this->matches[$name] = $value;
        }
        else if (is_array($name)) {
            $this->matches = array_merge($this->matches, $name);
        }
        return $this;
    }
    public function getRoute()
    {
        $route = [
            'method' => $this->method,
            'route' => $this->route,
            'prefix' => $this->prefix,
            'namespace' => $this->namespace,
            'name' => $this->name,
            'middleware' => $this->middleware,
            'variables' => $this->variables,
            'matches' => $this->matches,
            'callable' => $this->callable,
        ];
        $route['route'] = $this->prefix . $route['route'];
        if (is_array($route['callable'])) {
            $route['callable']['controller'] = $this->namespace . '\\' . $route['callable']['controller'];
        }
        return $route;
    }
    public function getPrefix()
    {
        return $this->prefix;
    }
    public function getRoutePath()
    {
        return $this->route;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function getMatches() :array
    {
        $defaultPattern = '(?<%s>[^\/]+)';
        $result = [];
        foreach ($this->variables as $variable) {
            $name = $variable['name'];
            $optional = '';
            if ($variable['is_optional']) {
                $optional = '?';
                $name .= $optional;
            }
            if (isset($this->matches[$variable['name']])) {
                $result[$name] = sprintf('%s(?<%s>%s)%s', $optional, $variable['name'],
                    addcslashes($this->matches[$variable['name']], '#'), $optional);
            } else {
                $result[$name] = $optional . sprintf($defaultPattern . $optional, $variable['name']);
            }
        }
        return $result;
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