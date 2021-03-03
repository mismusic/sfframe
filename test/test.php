<?php

$next = function ($request) {
    echo 'request convert response' . PHP_EOL;
};
$list = [
    function ($request, $next) {
        echo 'a' . $request . 'before' . PHP_EOL;
        $next($request);
        echo 'a' . $request . 'after' . PHP_EOL;
    },
    function ($request, $next) {
        echo 'b' . $request . 'before' . PHP_EOL;
        $next($request);
        echo 'b' . $request . 'after' . PHP_EOL;
    },
    function ($request, $next) {
        echo 'c' . $request . 'before' . PHP_EOL;
        $next($request);
        echo 'c' . $request . 'after' . PHP_EOL;
    },
];
$result = array_reduce(array_reverse($list), function ($next, $val) {
    return function ($request) use ($next, $val) {
        return $val($request, $next);
    };
}, $next);
$request = 1;
$result($request);
