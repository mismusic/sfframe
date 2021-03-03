<?php

namespace frame\core\database\query;

use frame\core\Config;
use frame\core\database\Mysql;

class BaseQuery
{
    protected $config;
    protected $prefix = '';
    protected $table;
    protected $pkId = 'id';
    protected $options = [];
    /**
     * @var Mysql
     */
    protected $mysql;
    protected $bindParams = [];

    const QUERY_WHERE_LOGIC_AND = 'and';
    const QUERY_WHERE_LOGIC_OR = 'or';

    public function __construct(Config $config, Mysql $mysql)
    {
        $this->config = $config;
        $dbConfig = $config->get('database');
        $this->prefix = $dbConfig['connections'][$dbConfig['default']]['prefix'] ?? '';
        $this->mysql = $mysql;
    }
    public function newQuery()
    {
        return new static($this->config, $this->mysql);
    }
    public function getTable()
    {
        return $this->table;
    }
    public function table(string $table)
    {
        $this->table = $this->tableNameHandler(trim($table));
        return $this;
    }
    public function name(string $table)
    {
        $this->table = $this->tableNameHandler($this->prefix . trim($table));
        return $this;
    }
    public function pkId(string $name)
    {
        $this->pkId = $name;
        return $this;
    }
    public function getPkIdName()
    {
        return $this->pkId;
    }
    protected function tableNameHandler(string $table)
    {
        if (empty($table)) {
            return '';
        }
        $table = strtr($table, ['`' => '']);
        $tableArr = explode(' ', $table);
        $tableArr = array_filter($tableArr);
        foreach ($tableArr as & $item) {
            if ($item !== 'as') {
                $item = sprintf('`%s`', $item);
            }
        }
        return join(' ', $tableArr);
    }
}