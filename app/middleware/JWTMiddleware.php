<?php

namespace app\middleware;

use app\exception\AppException;
use frame\core\middleware\interfaces\MiddlewareInterface;
use frame\core\Request;

class JWTMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next)
    {
        throw new AppException('jwt auth failed');
        return $next($request);
    }

}