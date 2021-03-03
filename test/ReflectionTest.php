<?php

namespace test;

use app\controller\Test;

class ReflectionTest
{
    public function __construct(string $foo, $name = 'bar')
    {
    }

    public function test(Test $test, $abc = null)
    {
    }

    public function emptyMethod()
    {
    }
}