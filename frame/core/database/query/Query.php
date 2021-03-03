<?php

namespace frame\core\database\query;

use app\exception\DatabaseException;
use frame\core\Config;
use frame\core\database\Mysql;

class Query extends BaseQuery
{
    public function __construct(Config $config, Mysql $mysql)
    {
        parent::__construct($config, $mysql);
        $this->options = [
            'select' => '*',
            'where' => [],
            'offset' => null,
            'limit' => null,
            'join' => [],
            'order' => [],
        ];
    }
    public function insert(array $data, bool $lastInsertId = false)
    {
        if (empty($data)) {
            throw new DatabaseException('query insert param data must exists');
        }
        if (empty($this->table)) {
            throw new DatabaseException('query missing table name');
        }
        $sql = $this->genInsertSql($data);
        $num = $this->mysql->execute($sql, $this->bindParams);
        if ($lastInsertId) {
            return $this->mysql->getInsertId();
        }
        return $num;
    }
    public function delete()
    {
        $sql = $this->genDeleteSql();
        return $this->mysql->execute($sql, $this->bindParams);
    }
    public function update(array $data)
    {
        $sql = $this->genUpdateSql($data);
        return $this->mysql->execute($sql, $this->bindParams);
    }
    public function updateBatch(array $data)
    {
        $sql = $this->genBatchUpdateSql($data);
        return $this->mysql->execute($sql);
    }
    public function get()
    {
        $sql = $this->getQuerySql();
        return $this->mysql->get($sql, $this->bindParams);
    }
    public function first()
    {
        $sql = $this->getQuerySql(false);
        return $this->mysql->first($sql, $this->bindParams);
    }
    public function where($field, $operation = null, $value = null)
    {
        $this->whereLogic($field, self::QUERY_WHERE_LOGIC_AND, $operation, $value);
        return $this;
    }
    public function orWhere($field, $operation = null, $value = null)
    {
        $this->whereLogic($field, self::QUERY_WHERE_LOGIC_OR, $operation, $value);
        return $this;
    }
    public function whereColumn(string $field, $operation, $value = null)
    {
        if (is_null($value)) {
            $whereColumn[] = [$field, '=', $operation, 'column'];
        } else {
            $whereColumn[] = [$field, $operation, $value, 'column'];;
        }
        $queryWhereColumn = $this->options['where']['and'] ?? [];
        $this->options['where']['and'] = array_merge($queryWhereColumn, $whereColumn);
        return $this;
    }
    public function join(string $table, string $rawSql, string $logic = 'inner')
    {
        $this->joinLogic($table, $rawSql, $logic);
        return $this;
    }
    public function leftJoin(string $table, string $rawSql)
    {
        $this->joinLogic($table, $rawSql, 'left');
        return $this;
    }
    public function rightJoin(string $table, string $rawSql)
    {
        $this->joinLogic($table, $rawSql, 'left');
        return $this;
    }
    public function field($fields = null)
    {
        if (is_null($fields)) {
            return $this;
        }
        if (! is_string($fields) && ! is_array($fields)) {
            throw new \InvalidArgumentException('query field argument type error');
        }
        if (is_string($fields)) {
            $fields = func_get_args();
        }
        if ($fields) {
            $fields = array_map([$this, 'fieldHandler'], $fields);
            $fields = join(',', $fields);
            $this->options['select'] = $fields;
        }
        return $this;
    }
    public function order($column, string $value = null)
    {
        if (! is_string($column) && ! is_array($column)) {
            throw new \InvalidArgumentException('query order argument type error');
        }
        if (is_string($column)) {
            if (is_null($value)) {
                throw new \InvalidArgumentException('query order argument value must not null');
            }
            $this->options['order'][$column] = $value;
        }
        if (is_array($column)) {
            $this->options['order'] = array_merge($this->options['order'], $column);
        }
        return $this;
    }
    public function offset(int $offset)
    {
        $this->options['offset'] = $offset;
        return $this;
    }
    public function limit(int $limit)
    {
        $this->options['limit'] = $limit;
        return $this;
    }
    public function count(string $column = '*')
    {
        $sql = $this->genAggrSql($column, 'count');
        return $this->mysql->value($sql, $this->bindParams);
    }
    public function sum(string $column)
    {
        $sql = $this->genAggrSql($column, 'sum');
        return $this->mysql->value($sql, $this->bindParams);
    }
    public function avg(string $column)
    {
        $sql = $this->genAggrSql($column, 'avg');
        return $this->mysql->value($sql, $this->bindParams);
    }
    public function max(string $column)
    {
        $sql = $this->genAggrSql($column, 'max');
        return $this->mysql->value($sql, $this->bindParams);
    }
    public function min(string $column)
    {
        $sql = $this->genAggrSql($column, 'min');
        return $this->mysql->value($sql, $this->bindParams);
    }
    public function value(string $column)
    {
        if (empty($column)) {
            throw new DatabaseException('query value argument column must not null');
        }
        $sql = $this->getQuerySql(false, $column);
        return $this->mysql->value($sql, $this->bindParams);
    }
    public function inc(string $column, $step = 1)
    {
        if (empty($column)) {
            throw new DatabaseException('query inc argument column must not null');
        }
        $sql = $this->genIncDec($column, $step, 'inc');
        return $this->mysql->execute($sql, $this->bindParams);
    }
    public function dec(string $column, $step = 1)
    {
        if (empty($column)) {
            throw new DatabaseException('query dec argument column must not null');
        }
        $sql = $this->genIncDec($column, $step, 'dec');
        return $this->mysql->execute($sql, $this->bindParams);
    }
    public function getQuerySql(bool $more = true, string $column = null)
    {
        if (empty($this->table)) {
            throw new DatabaseException('query missing table name');
        }
        $join = $this->parseJoin();
        $where = $this->parseWhere();

        $select = ! is_null($column) ? $this->fieldHandler($column) : $this->options['select'];
        $sql = sprintf('select %s from %s', $select, $this->table);
        if ($join) {
            $sql = sprintf('%s %s', $sql, $join);
        }
        if ($where) {
            $sql = sprintf('%s where %s', $sql, $where);
        }
        $limit = $this->options['limit'];
        $offset = $this->options['offset'];
        $order = $this->parseOrder();
        if ($order) {
            $sql = sprintf('%s %s', $sql, $order);
        }
        if ($more) {
            if (! is_null($limit) || ! is_null($offset)) {
                $limit = sprintf('limit %d,%d', $this->options['offset'], $limit);
                $sql = sprintf('%s %s', $sql, $limit);
            }
        } else {
            $sql = sprintf('%s limit 1', $sql);
        }
        return $sql;
    }
    public function select(string $sql, array $bindParams = [])
    {
        return $this->mysql->get($sql, $bindParams);
    }
    public function execute(string $sql, array $bindParams = [])
    {
        return $this->mysql->execute($sql, $bindParams);
    }
    protected function whereLogic($field, string $logic, $operation = null, $value = null)
    {
        $where = [];
        if (! is_string($field) && ! is_array($field) && ! is_callable($field)) {
            throw new \InvalidArgumentException('query where argument field type error');
        }
        if (! is_null($value) && ! is_numeric($value) && ! is_string($value) && ! is_array($value)) {
            throw new \InvalidArgumentException('query where argument value type error');
        }
        if (is_string($field)) {
            if (is_null($value)) {
                if (is_null($operation) || strtolower($operation) === 'null' ||  strtolower($operation) === 'not null')
                {
                    $operation = is_null($operation) ? 'null' : $operation;
                    $where[] = [$field, $operation, ''];
                } else {
                    $where[] = [$field, '=', $operation];
                }
            } else {
                $where[] = [$field, $operation, $value];
            }
        }
        if (is_array($field)) {
            if (is_array(reset($field))) {
                $where = array_merge($where, $field);
            } else {
                foreach ($field as $k => $val) {
                    $where[] = [$k, '=', $val];
                }
            }
        }
        if (is_callable($field)) {
            $where[] = $field;
        }
        if ($where) {
            $queryWhere = $this->options['where'][$logic] ?? [];
            $this->options['where'][$logic] = array_merge($queryWhere, $where);
        }
    }
    public function joinLogic(string $table, string $rawSql, string $logic)
    {
        $table = $this->tableNameHandler($table);
        $join = [];
        if (! in_array($logic, ['inner', 'left', 'right'])) {
            throw new \InvalidArgumentException('query joinLogic argument logic type error');
        }
        $join[] = [$logic, $table, $rawSql];
        if ($join) {
            $this->options['join'] = array_merge($this->options['join'], $join);
        }
    }
    public function parseWhere()
    {
        $whereSql = '';
        if (isset($this->options['where']['and']))
        {
            $whereAnd = $this->options['where']['and'];
            $whereAndSql = '';
            if (is_array($whereAnd)) {
                $whereAndSql = $this->parseWhereLogic($whereAnd, 'and');
            }
            $whereSql .= $whereAndSql;
        }
        if (isset($this->options['where']['or']))
        {
            $whereOr = $this->options['where']['or'];
            $whereOrSql = '';
            if (is_array($whereOr)) {
                $whereOrSql = $this->parseWhereLogic($whereOr, 'or');
            }
            if ($whereSql) {
                $whereOrSql = ' or ' . $whereOrSql;
            }
            $whereSql .= $whereOrSql;
        }
        return $whereSql;
    }
    protected function parseWhereLogic(array $wheres, string $logic)
    {
        if (empty($wheres)) {
            return '';
        }
        $whereString = '';
        foreach ($wheres as $k => $where) {
            if (is_callable($where)) {
                $whereClosure = $this->parseWhereClosure($where);
                if ($whereClosure) {
                    $whereClosure = sprintf('(%s)', $whereClosure);
                    $whereString = sprintf('%s %s %s', $whereString, $logic, $whereClosure);
                }
            } else {
                $whereOperHandler = $this->whereOperationHandler($where);
                if ($whereOperHandler) {
                    $whereString = sprintf('%s %s %s', $whereString, $logic, $whereOperHandler);
                }
            }
        }
        $whereString = ltrim($whereString, sprintf(' %s ', $logic));
        return $whereString;
    }
    public function parseJoin()
    {
        $join = $this->options['join'] ?? [];
        if (empty($join)) {
            return '';
        }
        $joinSql = '';
        $joinArr = [];
        foreach ($join as $joinItem) {
            if (is_array($joinItem) && count($joinItem) === 3) {
                list($logic, $table, $rawSql) = $joinItem;
                $joinArr[] = sprintf('%s join %s on %s', $logic, $table, $rawSql);
            }
        }
        if ($joinArr) {
            $joinSql = join(' ', $joinArr);
        }
        return $joinSql;
    }
    protected function parseOrder() :string
    {
        $order = '';
        if ($this->options['order']) {
            foreach ($this->options['order'] as $k => $val)
            {
                $k = $this->fieldHandler($k);
                $order .= sprintf('%s %s,', $k, $val);
            }
            $order = 'order by ' . rtrim($order, ',');
        }
        return $order;
    }
    protected function parseWhereClosure(callable $closure)
    {
        $newQuery = $this->newQuery();
        $closure($newQuery);  // 执行闭包函数
        $queryWhereSql = $newQuery->parseWhere();
        $this->bindParams = array_merge($this->bindParams, $newQuery->bindParams);
        return $queryWhereSql;
    }
    protected function genInsertSql(array $data)
    {
        if (empty($data)) {
            return '';
        }
        if (! is_array($data)) {
            $data = [$data];
        }
        $columns = sprintf('(`%s`)', join('`,`', array_keys(reset($data))));
        $sql = sprintf('insert into %s %s values', $this->table, $columns);
        $values = '';
        foreach ($data as $item) {
            $row = '(';
            foreach ($item as $val) {
                $row .= '?,';
                $this->bindParams[] = $val;
            }
            $row = sprintf('%s)', rtrim($row, ','));
            if (empty($values)) {
                $values = $row;
            } else {
                $values = sprintf('%s,%s', $values, $row);
            }
        }
        $sql = sprintf('%s %s', $sql, $values);
        return $sql;
    }
    protected function genDeleteSql()
    {
        $where = $this->parseWhere();
        $sql = sprintf('delete from %s', $this->table);
        if ($where) {
            $sql = sprintf('%s where %s', $sql, $where);
        }
        return $sql;
    }
    protected function genUpdateSql(array $data)
    {
        if (empty($data)) {
            return '';
        }
        $where = $this->parseWhere();
        $sql = sprintf('update %s set', $this->table);
        $updBindParams = [];
        foreach ($data as $k => $val) {
            $k = trim($k);
            $sql = sprintf('%s `%s` = ? ,', $sql, $k);
            $updBindParams[] = is_numeric($val) ? $val : strval($val);
        }
        $this->bindParams = array_merge($updBindParams, $this->bindParams);
        $sql = rtrim($sql, ' ,');
        if ($where) {
            $sql = sprintf('%s where %s', $sql, $where);
        }
        return $sql;
    }
    protected function genAggrSql(string $column, string $aggrType)
    {
        if (! in_array($aggrType, ['count', 'max', 'min', 'sum', 'avg'])) {
            throw new DatabaseException('query aggregation type error');
        }
        if (empty($this->table)) {
            throw new DatabaseException('query missing table name');
        }
        $column = $this->fieldHandler($column);
        $sql = sprintf('select %s(%s) as `%s` from %s', $aggrType, $column, $aggrType, $this->table);
        $join = $this->parseJoin();
        $where = $this->parseWhere();
        if ($join) {
            $sql = sprintf('%s %s', $sql, $join);
        }
        if ($where) {
            $sql = sprintf('%s where %s', $sql, $where);
        }
        $limit = $this->options['limit'];
        $offset = $this->options['offset'];
        if (! is_null($limit) || ! is_null($offset)) {
            $limit = sprintf('limit %d,%d', $this->options['offset'], $limit);
            $sql = sprintf('%s %s', $sql, $limit);
        }
        return $sql;
    }
    protected function genIncDec(string $column, int $step, $type)
    {
        if (! in_array($type, ['inc', 'dec'])) {
            throw new DatabaseException('query genIncDec argument type error');
        }
        if (empty($this->table)) {
            throw new DatabaseException('query missing table name');
        }
        $type = $type === 'inc' ? '+' : '-';
        $column = $this->fieldHandler($column);
        $sql = sprintf('update %1$s set %2$s = %2$s %3$s %4$s', $this->table, $column, $type, $step);
        $join = $this->parseJoin();
        $where = $this->parseWhere();
        if ($join) {
            $sql = sprintf('%s %s', $sql, $join);
        }
        if ($where) {
            $sql = sprintf('%s where %s', $sql, $where);
        }
        $limit = $this->options['limit'];
        $offset = $this->options['offset'];
        if (! is_null($limit) || ! is_null($offset)) {
            $limit = sprintf('limit %d,%d', $this->options['offset'], $limit);
            $sql = sprintf('%s %s', $sql, $limit);
        }
        return $sql;
    }
    /**
     * 生成批量更新的sql语句
     * @param array $data
     * @return string
     * @throws DatabaseException
     */
    protected function genBatchUpdateSql(array $data)
    {
        $tableName = $this->table;
        $pkName = $this->pkId;
        if (! is_array(reset($data))) {  // 如果数据只是一维数组就把它转变成一个二维数组
            $data = [$data];
        }
        if (! array_contain_keys($data, [$pkName])) {
            throw new DatabaseException(sprintf('sql column %s not exists', $pkName));
        }
        $uColumns = array_keys(reset($data));  // 获取所有需要更新的字段名
        $pIndex = array_search($pkName, $uColumns);
        unset($uColumns[$pIndex]);  // 去掉主键字段值
        $ids = '"' . join('","', array_column($data, $pkName)) . '"';
        $sql = "update {$tableName} set ";
        $case = '';
        foreach ($uColumns as $uColumn) {
            $case .= "`$uColumn` = case `{$pkName}` ";
            $when = '';
            foreach ($data as $item) {
                $value = addslashes($item[$uColumn]);  // 对要更新的值进行过滤处理
                $when .= "when \"{$item[$pkName]}\" then \"$value\" ";
            }
            $case .= $when . 'end,';
        }
        $case = rtrim($case, ',');  // 去掉最后多余的逗号 数据示例 name = case id when 1 then test when 2 then foo end
        $sql .= $case . " where {$pkName} in({$ids})";
        return $sql;
    }
    protected function fieldHandler(string $field)
    {
        $field = trim($field);
        if (strpos($field, '.') !== false) {
            $fieldArr = explode('.', $field);
            $fieldArr = array_map(function ($val) {
                if ($val === '*') {
                    return $val;
                }
                return sprintf('`%s`', $val);
            }, $fieldArr);
            $field = join('.', $fieldArr);
        } else {
            if ($field !== '*') {
                $field = "`{$field}`";
            }
        }
        return $field;
    }
    protected function whereOperationHandler(array $where)
    {
        $whereString = '';
        $whereCount = count($where);
        if (is_array($where) && $whereCount >= 3) {
            list($field, $operation, $value) = $where;
            $field = $this->fieldHandler($field);
            $operation = strtolower(trim($operation));
            $operVal = '';
            if (in_array($operation, ['>', '<', '=', '>=', '<=', '<>', '!=='])) {
                $operVal = sprintf('%s ?', $operation);
            }
            else if ($operation === 'in' || $operation === 'not in') {
                if (! is_array($value)) {
                    throw new DatabaseException('query where operation in value must is array');
                }
                if ($value) {
                    $valCount = count($value);
                    $bindVal = rtrim(str_repeat('?,', $valCount), ',');
                    $operVal = sprintf('%s (%s)', $operation, $bindVal);
                }
            }
            else if ($operation === 'instr') {
                $operVal = sprintf('instr(%s, ?) > 0', $field);
            }
            else if ($operation === 'not instr') {
                $operVal = sprintf('instr(%s, ?) = 0', $field);
            }
            else if ($operation === 'like' || $operation === 'not like') {
                $operVal = sprintf('%s ?', $operation);
                $value = strval($value);
            }
            else if ($operation === 'between' || $operation === 'not between') {
                if (! is_array($value)) {
                    throw new DatabaseException('query where operation between value must is array');
                }
                if ($value) {
                    $valCount = count($value);
                    if ($valCount !== 2) {
                        throw new DatabaseException('query where operation between value must is 2 argument');
                    }
                    $bindVal = join(' and ', str_split(str_repeat('?', $valCount)));
                    $operVal = sprintf('%s %s', $operation, $bindVal);
                }
            }
            else if ($operation === 'null' || $operation === 'not null') {
                $operVal = sprintf('is %s', $operation);
            }
            else if ($operation === 'exp') {
                $operVal = sprintf('%s', trim(strval($value)));
            }
            else {
                throw new DatabaseException('query where operation type not exists');
            }
            if ($operVal) {
                if ($whereCount === 4 && $where[3] = 'column') {
                    $value = $this->fieldHandler($value);
                    $whereString .= sprintf('%s %s %s', $field, $operation, $value);
                } else {
                    if (in_array($operation, ['instr', 'not instr'])) {
                        $whereString .= $operVal;
                    } else {
                        $whereString .= sprintf('%s %s', $field, $operVal);
                    }
                    if (! in_array($operation, ['null', 'not null', 'exp'])) {
                        if (is_array($value)) {
                            if (! empty($value)) {
                                $this->bindParams = array_merge($this->bindParams, $value);
                            }
                        } else {
                            $this->bindParams[] = $value;
                        }
                    }

                }
            }
            return $whereString;
        }
    }
}