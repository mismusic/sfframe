<?php

namespace test;

class A
{
    public $b;
    public $foo;
    public function __construct(B $b)
    {
        $this->b = $b;
        $this->foo = '魔女宅急便';
    }
}