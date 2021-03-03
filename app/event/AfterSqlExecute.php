<?php

namespace app\event;

use frame\core\database\interfaces\DBInterface;

class AfterSqlExecute
{
    public $data;

    public function __construct(DBInterface $db)
    {
        $this->data = $db;
    }
}