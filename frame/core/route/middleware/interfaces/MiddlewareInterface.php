<?php

namespace frame\core\route\middleware\interfaces;

use frame\core\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next);
}