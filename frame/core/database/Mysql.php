<?php

namespace frame\core\database;

use app\exception\DatabaseException;
use app\facade\Log;
use frame\core\App;
use frame\core\Config;
use frame\core\database\drive\Pdo;
use frame\core\database\query\Query;

class Mysql extends Pdo
{
    public function __construct(Config $config)
    {
        $this->config = $config;
        $dbDefault = $this->config->get('database.default');
        $dbConfig = $this->config->get(sprintf('database.connections.%s', $dbDefault));
        if (empty($config)) {
            throw new DatabaseException('Configuration file not found');
        }
        $dsn = sprintf('mysql:dbname=%s;host=%s;port=%s;charset=%s', $dbConfig['db_name'],
            $dbConfig['host'], $dbConfig['port'], $dbConfig['charset']);
        $options = $this->options + $dbConfig['options'];
        try {
            $this->pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        } catch (\PDOException $e) {
            $errStr = sprintf('mysql connection failed %s', $e->getMessage());
            Log::write('db', $errStr);
            throw new DatabaseException($errStr);
        }
    }
    public function getSql()
    {
        return $this->sql;
    }
    public function getBindParams(): array
    {
        return $this->bindParams;
    }
    public function rumTime(): string
    {
        return number_format($this->executeTime['endRunTime'] - $this->executeTime['startRunTime'],
            6, '.', '');
    }
    public function execute(string $sql, array $bindParams = [])
    {
        $this->bindExecute($sql, $bindParams);
        return $this->pdoStatement->rowCount();
    }
    public function select(string $sql, array $bindParams = [])
    {
        return $this->get($sql, $bindParams);
    }
    public static function query() :Query
    {
        $query = App::getContainer()->resolve(Query::class);
        return $query->newQuery();
    }
    public static function name(string $table) :Query
    {
        return self::query()->name($table);
    }
    public static function table(string $table) :Query
    {
        return self::query()->table($table);
    }
}