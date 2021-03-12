<?php

namespace frame\core\route;

class RouteGroup extends Router
{
    public function addRule(string $route, $action, $method = 'GET') :Route
    {
        $router = Route::newInstance();
        $router->addRule($route, $action, $method);
        $this->routeCollection[] = $router;
        return $router;
    }
    public function deleteRule(string $route) :bool
    {
        if (! isset($this->routeCollection[$route])) {
            return false;
        }
        unset($this->routeCollection[$route]);
        return true;
    }
    public function group(callable $callable)
    {
        $this->group = $callable;
        return $this;
    }
    public function getRouteList() :array
    {
        $groupRoutes = [];
        if (! is_null($this->group)) {
            $newRouteDispatcher = RouteDispatcher::newInstance();
            $group = $this->group;
            $group($newRouteDispatcher);
            $groupRoutes = $newRouteDispatcher->getRouteRecursive();
            foreach ($groupRoutes as $groupRoute) {
                $groupRouteInfo = $groupRoute->getRoute();
                $prefix = $this->prefix . $groupRouteInfo['prefix'];
                $namespace = $this->namespace . $groupRouteInfo['namespace'];
                $name = $this->name . $groupRouteInfo['name'];
                $groupRoute->prefix($prefix)
                    ->namespace($namespace)
                    ->middleware($this->middleware)
                    ->name($name);
            }
        }
        $routes = array_merge($this->routeCollection, $groupRoutes);
        return $routes;
    }
}