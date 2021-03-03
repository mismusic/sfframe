<?php

namespace test;

class B
{
    public $c;
    public function __construct(C $c)
    {
        $this->c = $c;
    }
}