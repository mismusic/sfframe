<?php

namespace frame\core\route;

use app\exception\AppException;
use app\exception\RouteException;
use frame\core\Container;
use frame\core\exception\handler\ExceptionHandler;
use frame\core\Request;
use frame\utils\CoreReflection;
use frame\core\middleware\interfaces\MiddlewareInterface;

class RouteDispatcher extends Router
{
    protected $routeGroup = [];
    /**
     * @var RouteGroup
     */
    protected $group;
    protected $router;
    /**
     * @var Route
     */
    protected $route;
    protected $parameters = [];

    public function __construct()
    {
        $this->initRouteGroup();
    }
    public function prefix(string $prefix)
    {
        if (empty($prefix)) {
            return $this;
        }
        $this->group->prefix($prefix);
        return $this;
    }
    public function namespace(string $namespace)
    {
        if (empty($namespace)) {
            return $this;
        }
        $this->group->namespace($namespace);
        return $this;
    }
    public function name(string $name)
    {
        if (empty($name)) {
            return $this;
        }
        $this->group->name($name);
        return $this;
    }
    public function middleware($middleware)
    {
        if (empty($middleware)) {
            return $this;
        }
        $this->group->middleware($middleware);
        return $this;
    }
    public function group(callable $callable)
    {
        $routeGroup = $this->group->group($callable);
        $this->routeGroup[] = $routeGroup;
        // init route group
        $this->initRouteGroup();
        return $routeGroup;
    }
    public function addRule(string $route, $action, $method = 'GET')
    {
        $route = $this->group->addRule($route, $action, $method);
        $routeGroup = $this->group;
        $this->routeGroup[] = $routeGroup;
        $this->initRouteGroup();
        return $route;
    }
    public function get(string $route, $action)
    {
        return $this->addRule($route, $action, 'GET');
    }
    public function post(string $route, $action)
    {
        return $this->addRule($route, $action, 'POST');
    }
    public function put(string $route, $action)
    {
        return $this->addRule($route, $action, 'PUT');
    }
    public function patch(string $route, $action)
    {
        return $this->addRule($route, $action, 'PATCH');
    }
    public function delete(string $route, $action)
    {
        return $this->addRule($route, $action, 'DELETE');
    }
    public function options(string $route, $action)
    {
        return $this->addRule($route, $action, 'OPTIONS');
    }
    public function any(string $route, $action)
    {
        return $this->addRule($route, $action, '*');
    }
    public function only(string $route, $action, array $method)
    {
        return $this->addRule($route, $action, $method);
    }
    public function getRouteGroup() :array
    {
        return $this->routeGroup;
    }
    public function getRouteList() :array
    {
        $routes = $this->getRouteRecursive();
        $result = [];
        foreach ($routes as $route) {
            $prefix = $route->getPrefix();
            $routePath = $route->getRoutePath();
            foreach ($route->getMethod() as $method) {
                $key = sprintf('%s%s%s', $method, $prefix, $routePath);
                $result[$key] = $route;
            }
        }
        return $result;
    }
    public function getRouteRecursive() :array
    {
        $routes = [];
        foreach ($this->routeGroup as $routeGroup) {
            $routes = array_merge($routes, $routeGroup->getRouteList());
        }
        return $routes;
    }
    public function getMiddleware(string $route = null, string $method = 'GET') :array
    {
        if (is_null($route)) {
            $middlewareList = $this->route ? $this->route->getRoute()['middleware'] : [];
        } else {
            $method = strtoupper($method);
            $route = $method . '/' . trim($route, '/');
            $routes = $this->getRouteList();
            $middlewareList = isset($routes[$route]) ? $routes[$route]['middleware'] : [];
        }
        return $middlewareList;
    }
    public function getRouter() :array
    {
        return $this->router;
    }
    public function getRoute() :Route
    {
        return $this->route;
    }
    public function run(Request $request)
    {
        $this->exceptionHandler($request);
        $this->findRoute($request);
        $middlewareList = $this->getMiddleware();
        $this->checkMiddleware($middlewareList);
        return $this->middlewarePipe($request, $middlewareList);
    }
    protected function exceptionHandler(Request $request) :void
    {
        set_exception_handler(function (\Throwable $throwable) use ($request) {
            $exceptionHandler = Container::getInstance()->resolve(ExceptionHandler::class);
            if (! $exceptionHandler instanceof ExceptionHandler) {
                echo new AppException('Exception handler class not found');
            } else {
                $exceptionHandler->report($request, $throwable);
                $response = $exceptionHandler->printString($request, $throwable);
                $response->send();
            }
        });
    }
    protected function findRoute(Request $request)
    {
        $this->router = $request->getRouter();
        $method = $this->router['method'];
        $router = $this->router['route'];
        foreach ($this->getRouteList() as $routeItem) {
            $routeInfo = $routeItem->getRoute();
            $routePath = $routeInfo['route'];
            $matches = $routeItem->getMatches();
            foreach ($matches as $matName => $matVal) {
                $routePath = str_replace(sprintf('<%s>', $matName), $matVal, $routePath);
            }
            $routePath = sprintf('#^%s$#iu', $routePath);
            if (preg_match($routePath, $router, $parameters)) {
                $this->route = $routeItem;
                $request->setRouteDispatcher($this);
                if (in_array($method, $routeInfo['method'])) {
                    $this->parameters = array_filter($parameters, function ($val, $k) {
                        return is_string($k) && $val;
                    }, ARRAY_FILTER_USE_BOTH);
                    break 1;
                }
            }
        }
        // check route method
        if ($this->route && ! $this->checkMethod()) {
            throw new RouteException(sprintf('Routing method not allowed, allowed %s', $this->router['method']), 405);
        }
    }
    protected function checkMiddleware(array $middlewareList)
    {
        foreach ($middlewareList as $middleware) {
            if (! CoreReflection::implementsInterface($middleware, MiddlewareInterface::class))
            {
                throw new \LogicException(sprintf('route middleware must implement %s', MiddlewareInterface::class));
            }
        }
    }
    protected function middlewarePipe(Request $request, array $middlewareList)
    {
        $middlewareList = array_reverse($middlewareList);
        $init = function ($request) {
            return $this->handleRequest($request);
        };
        $next = array_reduce($middlewareList, function ($next, $handle) {
            return function ($request) use ($next, $handle) {
                $handle = Container::getInstance()->make($handle);
                return $handle->handle($request, $next);
            };
        }, $init);
        return $next($request);
    }
    protected function handleRequest(Request $request)
    {
        if (is_null($this->route)) {
            $response = $this->handleController($request);
        } else {
            if (is_callable($this->route->getRoute()['callable'])) {
                $response = $this->handleClosure($request);
            } else {
                $response = $this->handleController($request);
            }

        }
        return $response;
    }
    protected function handleClosure(Request $request)
    {
        $reqParams = $this->getInjectParams($request);
        $callable = $this->route->getRoute()['callable'];
        $container = Container::getInstance();
        return $container->resolveClosure($callable, $reqParams);
    }
    protected function handleController(Request $request)
    {
        if (is_null($this->route)) {
            $handle = $request->parseRouter();
        } else {
            $handle = $this->route->getRoute()['callable'];
        }
        $handle = array_values($handle);
        [$controller, $action] = $handle;
        $container = Container::getInstance();
        $controllerBasename = class_name($controller);
        $controller = str_replace($controllerBasename, ucfirst($controllerBasename), $controller);
        $classFilePath = str_replace('\\', '/', ROOT_PATH . $controller) . ".php";
        if (! file_exists($classFilePath) || ! class_exists($controller)) {
            throw new AppException('app run error controller not exists');
        }
        if (! method_exists($controller, $action)) {
            throw new AppException('app run error method not exists');
        }
        $params = $this->getInjectParams($request);
        return $container->resolveMethod($controller, $action, $params);
    }
    protected function getInjectParams(Request $request) :array
    {
        $method = $request->getRouter()['method'];
        $reqMethod = $method === Route::HTTP_METHOD_GET ? 'get' : 'post';
        $reqParams = $request->$reqMethod();
        return array_merge($reqParams, $this->parameters);
    }
    protected function checkMethod() :bool
    {
        if ($this->route && $this->router) {
            $routeInfo = $this->getRoute()->getRoute();
            if (in_array($this->router['method'], $routeInfo['method'])) {
                return true;
            }
        }
        return false;
    }
    public function __call($name, $arguments)
    {
        return $this->group->$name(... $arguments);
    }

    protected function initRouteGroup()
    {
        $this->group = RouteGroup::newInstance();
    }
}