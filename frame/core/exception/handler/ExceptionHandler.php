<?php

namespace frame\core\exception\handler;

use app\exception\AppException;
use app\exception\DatabaseException;
use app\exception\RouteException;
use app\facade\Config;
use app\facade\Log;
use frame\core\Request;
use frame\core\Response;

class ExceptionHandler
{
    protected $exceptions = [
        AppException::class,
        RouteException::class,
        DatabaseException::class,
    ];

    public function report(Request $request, \Throwable $throwable)
    {
        if (Config::get('app.debug')) {
            foreach ($this->exceptions as $exception) {
                if ($throwable instanceof $exception) {
                    Log::error($throwable->__toString());
                }
            }
        }
    }
    public function printString(Request $request, \Throwable $throwable) :Response
    {
        if (Config::get('app.debug') || $request->isResponseJson()) {
            return response()->json($this->toArray($throwable));
        }
        return response()->body($throwable->__toString());
    }

    protected function toArray(\Throwable $throwable)
    {
        return [
            'code' => $throwable->getCode(),
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace(),
            'trace_string' => $throwable->getTraceAsString(),
        ];
    }
}