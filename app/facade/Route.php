<?php

namespace app\facade;

use frame\core\route\Route as RouteRule;
use frame\core\route\RouteDispatcher;
use frame\core\route\RouteGroup;

/**
 * Class Route
 * @see RouteDispatcher
 * @mixin RouteDispatcher
 * @method static RouteDispatcher prefix(string $prefix)
 * @method static RouteDispatcher namespace(string $namespace)
 * @method static RouteDispatcher name(string $name)
 * @method static RouteDispatcher middleware($middleware)
 * @method static RouteGroup group(callable $callable)
 * @method static Route addRule(string $route, $action, $method = 'GET')
 * @method static Route get(string $route, $action)
 * @method static Route post(string $route, $action)
 * @method static Route put(string $route, $action)
 * @method static Route patch(string $route, $action)
 * @method static Route delete(string $route, $action)
 * @method static Route options(string $route, $action)
 * @method static Route any(string $route, $action)
 * @method static Route only(string $route, $action, array $method)
 * @method static array getRouteGroup()
 * @method static array getRouteList()
 * @method static array getMiddleware(string $route, string $method = 'GET')
 * @method static array getRouter()
 * @method static Route getRoute()
 * @package app\facade
 */
class Route extends BaseFacade
{
    public static function getFacade()
    {
        return 'route';
    }
}