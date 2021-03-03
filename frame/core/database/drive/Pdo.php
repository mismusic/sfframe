<?php

namespace frame\core\database\drive;

use app\event\AfterSqlExecute;
use app\facade\Event;
use frame\core\Config;
use frame\core\database\interfaces\DBInterface;
use frame\core\database\query\Query;

abstract class Pdo implements DBInterface
{
    /**
     * @var \PDO
     */
    protected $pdo;
    /**
     * @var \PDOStatement
     */
    protected $pdoStatement;
    /**
     * @var Config
     */
    protected $config;
    protected $options = [
        \PDO::ATTR_TIMEOUT => 2.5,
        \PDO::ATTR_PERSISTENT => true,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ];
    protected $sql = '';
    protected $bindParams = [];
    protected $i = 0;
    protected $executeTime = [
        'startRunTime' => 0.000000,
        'endRunTime' => 0.000000,
    ];

    abstract public function __construct(Config $config);
    abstract public static function query() :Query;
    abstract public function select(string $sql, array $bindParams = []);
    abstract public function execute(string $sql, array $bindParams = []);
    abstract public function getSql();
    abstract public function getBindParams(): array;
    abstract public function rumTime() :string;
    public function startRunTime()
    {
        $this->executeTime['startRunTime'] = microtime(true);
    }
    public function endRunTime()
    {
        $this->executeTime['endRunTime'] = microtime(true);
    }
    public function beginTrans()
    {
        return $this->pdo->beginTransaction();
    }
    public function commit()
    {
        return $this->pdo->commit();
    }
    public function rollback()
    {
        return $this->pdo->rollBack();
    }
    public function get(string $sql, array $bindParams = [])
    {
        $this->bindExecute($sql, $bindParams);
        return $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function first(string $sql, array $bindParams = [])
    {
        $this->bindExecute($sql, $bindParams);
        return $this->pdoStatement->fetch(\PDO::FETCH_ASSOC);
    }
    public function value(string $sql, array $bindParams = [])
    {
        $this->bindExecute($sql, $bindParams);
        return $this->pdoStatement->fetchColumn();
    }
    public function getInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    public function setAttr(int $attr, $value)
    {
        return $this->pdo->setAttribute($attr, $value);
    }
    public function getAttr($attr = null)
    {
        if (! is_string($attr) && ! is_array($attr) && ! is_null($attr)) {
            throw new \InvalidArgumentException('pdo get attribute param type error');
        }
        if (is_null($attr) || is_array($attr)) {
            $attributes = array(
                "AUTOCOMMIT", "ERRMODE", "CASE", "CLIENT_VERSION", "CONNECTION_STATUS",
                "ORACLE_NULLS", "PERSISTENT", "SERVER_INFO", "SERVER_VERSION"
            );
            if (is_array($attr)) {
                $attributes = $attr;
            }
            $result = [];
            foreach ($attributes as $attribute) {
                $attr = constant("PDO::ATTR_{$attribute}");
                $result[$attr] = $this->pdo->getAttribute($attr);
            }
            return $result;
        }
        if (is_string($attr)) {
            $attr = strtoupper($attr);
            return $this->pdo->getAttribute(constant("PDO::ATTR_{$attr}"));
        }
    }
    protected function bindExecute(string $sql, array $bindParams = []) :bool
    {
        $this->initProperty();
        $this->bindParams = $bindParams;
        $this->sql = preg_replace_callback('#( \? | (?>:[a-z_][\w]*) )#ix', [$this, 'parseBindSql'], $sql);
        $this->pdoStatement = $this->pdo->prepare($sql);
        $isNameBind = $this->isNameBind($bindParams);
        foreach ($bindParams as $k => $bindParam) {
            if ($isNameBind) {
                $this->pdoStatement->bindValue(':' . $k, $bindParam);
            } else {
                $this->pdoStatement->bindValue($k + 1, $bindParam);
            }
        }
        $this->startRunTime();
        $result = $this->pdoStatement->execute();
        $this->endRunTime();
        // event trigger record log
        Event::dispatch(AfterSqlExecute::class);
        return $result;
    }
    protected function isNameBind(array $bindParams = []) :bool
    {
        if (empty($bindParams)) {
            return false;
        }
        if (! is_numeric(reset(array_keys($bindParams)))) {
            return true;
        }
        return false;
    }
    protected function parseBindSql(array $mat)
    {
        if (isset($mat[0])) {
            $value = $mat[0];
            if ($this->isNameBind($this->bindParams)) {
                $bindKey = ltrim($mat[0], ':');
                if (isset($this->bindParams[$bindKey])) {
                    $value = $this->bindParams[$bindKey];
                }
            } else {
                if (isset($this->bindParams[$this->i])) {
                    $value = $this->bindParams[$this->i];
                    $this->i ++;
                }
            }
            if (is_string($value)) {
                $value = sprintf('"%s"', $value);
            }
            return $value;
        }
    }
    protected function initProperty()
    {
        $this->sql = '';
        $this->bindParams = [];
        $this->i = 0;
        $this->executeTime = [
            'startRunTime' => 0.000000,
            'endRunTime' => 0.000000,
        ];
    }
}