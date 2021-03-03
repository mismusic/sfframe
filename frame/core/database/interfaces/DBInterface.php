<?php

namespace frame\core\database\interfaces;

use frame\core\database\query\Query;

interface DBInterface
{
    public static function query() :Query;
    public function select(string $sql, array $bindParams = []);
    public function execute(string $sql, array $bindParams = []);
    public function getSql();
    public function getBindParams() :array;
    public function rumTime();
    public function beginTrans();
    public function commit();
    public function rollback();
    public function get(string $sql, array $bindParams = []);
    public function first(string $sql, array $bindParams = []);
    public function value(string $sql, array $bindParams = []);
    public function getInsertId();
}