<?php

namespace app\middleware;

use app\exception\AppException;
use frame\core\Container;
use frame\core\Request;
use frame\core\middleware\interfaces\MiddlewareInterface;
use frame\core\Response;

class ApiMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next)
    {
        throw new AppException('api handle failed');
        echo sprintf('%s Before handle', __CLASS__) . PHP_EOL;
        $response = $next($request);
        echo sprintf('%s After handle', __CLASS__) . PHP_EOL;
        if (! is_object($response) || $response instanceof Response) {
            $response = Container::getInstance()
                ->resolve('response')
                ->header('app', 'before')
                ->body($response);
        }
        return $response;
    }

}