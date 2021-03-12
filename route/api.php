<?php

use \app\facade\Route;

Route::name('api.')
    ->prefix('/api')
    ->namespace('app\\controller\\test')
    ->middleware([
        \app\middleware\ApiMiddleware::class,
        \app\middleware\JWTMiddleware::class,
    ])
    ->group(function (\frame\core\route\RouteDispatcher $route) {
        $route->only('user', ['User', 'index'], ['get', 'post'])->name('test.user');
    });