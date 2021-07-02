<?php

namespace Mix\Database;

use Mix\Database\Helper\BuildHelper;

/**
 * Class QueryBuilder
 * @package Mix\Database
 */
class QueryBuilder
{

    /**
     * 连接
     * @var ConnectionInterface
     */
    public $conn;

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var array
     */
    protected $select = [];

    /**
     * @var array
     */
    protected $join = [];

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var array
     */
    protected $group = [];

    /**
     * @var array
     */
    protected $having = [];

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var string
     */
    protected $lock = '';

    /**
     * QueryBuilder constructor.
     * @param ConnectionInterface $conn
     */
    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function table(string $table): QueryBuilder
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param string ...$fields
     * @return $this
     */
    public function select(string ...$fields): QueryBuilder
    {
        $this->select = array_merge($this->select, $fields);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @param ...$args
     * @return $this
     */
    public function join(string $table, string $on, ...$args): QueryBuilder
    {
        array_push($this->join, ['INNER JOIN', $table, $on, $args]);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @param ...$args
     * @return $this
     */
    public function leftJoin(string $table, string $on, ...$args): QueryBuilder
    {
        array_push($this->join, ['LEFT JOIN', $table, $on, $args]);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @param ...$args
     * @return $this
     */
    public function rightJoin(string $table, string $on, ...$args): QueryBuilder
    {
        array_push($this->join, ['RIGHT JOIN', $table, $on, $args]);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @param ...$args
     * @return $this
     */
    public function fullJoin(string $table, string $on, ...$args): QueryBuilder
    {
        array_push($this->join, ['FULL JOIN', $table, $on, $args]);
        return $this;
    }

    /**
     * @param string $expr
     * @param ...$args
     * @return $this
     */
    public function where(string $expr, ...$args): QueryBuilder
    {
        array_push($this->where, ['AND', $expr, $args]);
        return $this;
    }

    /**
     * @param string $expr
     * @param ...$args
     * @return $this
     */
    public function or(string $expr, ...$args): QueryBuilder
    {
        array_push($this->where, ['OR', $expr, $args]);
        return $this;
    }

    /**
     * @param string $field
     * @param string $order
     * @return $this
     */
    public function order(string $field, string $order): QueryBuilder
    {
        if (!in_array($order, ['asc', 'desc'])) {
            throw new \RuntimeException('Sort can only be asc or desc.');
        }
        array_push($this->order, [$field, strtoupper($order)]);
        return $this;
    }

    /**
     * @param string ...$fields
     * @return $this
     */
    public function group(string ...$fields): QueryBuilder
    {
        $this->group = array_merge($this->group, $fields);
        return $this;
    }

    /**
     * @param string $expr
     * @param ...$args
     * @return $this
     */
    public function having(string $expr, ...$args): QueryBuilder
    {
        array_push($this->having, [$expr, $args]);
        return $this;
    }

    /**
     * offset
     * @param int $length
     * @return $this
     */
    public function offset(int $length): QueryBuilder
    {
        $this->offset = $length;
        return $this;
    }

    /**
     * limit
     * @param int $length
     * @return $this
     */
    public function limit(int $length): QueryBuilder
    {
        $this->limit = $length;
        return $this;
    }

    /**
     * 意向排它锁
     * @return $this
     */
    public function lockForUpdate(): QueryBuilder
    {
        $this->lock = 'FOR UPDATE';
        return $this;
    }

    /**
     * 意向共享锁
     * @return $this
     */
    public function sharedLock(): QueryBuilder
    {
        $this->lock = 'LOCK IN SHARE MODE';
        return $this;
    }

    /**
     * 预处理
     * @return Connection
     */
    protected function prepare()
    {
        $sqls = [];
        // select
        if ($this->select) {
            $select = implode(', ', $this->select);
            $sqls[] = ["SELECT {$select}"];
        } else {
            $sqls[] = ["SELECT *"];
        }
        // table
        if ($this->table) {
            $sqls[] = ["FROM {$this->table}"];
        }
        if ($this->join) {
            foreach ($this->join as $item) {
                list($type, $table, $on) = $item;
                $condition = BuildHelper::joinOn($on);
                $sqls[] = ["{$type} {$table} ON {$condition}"];
            }
        }
        // where
        if ($this->where) {
            list($subSql, $subParams) = BuildHelper::where($this->where);
            $sqls[] = ["WHERE {$subSql}", 'params' => $subParams];
        }
        // group
        if ($this->group) {
            $sqls[] = ["GROUP BY " . implode(', ', $this->group)];
        }
        // having
        if ($this->having) {
            $subSql = [];
            foreach ($this->having as $item) {
                list($field, $operator, $condition) = $item;
                $subSql[] = "{$field} {$operator} {$condition}";
            }
            $subSql = count($subSql) == 1 ? array_pop($subSql) : implode(' AND ', $subSql);
            $sqls[] = ["HAVING {$subSql}"];
        }
        // order
        if ($this->order) {
            $subSql = [];
            foreach ($this->order as $item) {
                list($field, $order) = $item;
                $subSql[] = "{$field} {$order}";
            }
            $sqls[] = ["ORDER BY " . implode(', ', $subSql)];
        }
        // limit and offset
        if ($this->limit > 0) {
            $sqls[] = ['LIMIT :__offset, :__limit', 'params' => ['__offset' => $this->offset, '__limit' => $this->limit]];
        }
        // lock
        if ($this->lock) {
            $sqls[] = [$this->lock];
        }
        // 返回
        return $this->conn->raw($sqls);
    }

    /**
     * 返回多行
     * @return array
     */
    public function get()
    {
        return $this->prepare()->queryAll();
    }

    /**
     * 返回一行
     * @return mixed
     */
    public function first()
    {
        return $this->prepare()->queryOne();
    }

    /**
     * 返回单个值
     * @param string $field
     * @return mixed
     * @throws \PDOException
     */
    public function value(string $field)
    {
        $result = $this->prepare()->queryOne();
        if (empty($result)) {
            return $result;
        }
        $isArray = is_array($result);
        if (($isArray && !isset($result[$field])) || (!$isArray && !isset($result->$field))) {
            throw new \PDOException(sprintf('Field %s not found', $field));
        }
        return $isArray ? $result[$field] : $result->$field;
    }

    /**
     * 更新
     * @param array $data
     * @return $this
     */
    public function updates(array $data)
    {
        if (!BuildHelper::isMulti($where)) {
            $where = [$where];
        }
        list($dataSql, $dataParams) = BuildHelper::data($data);
        list($whereSql, $whereParams) = BuildHelper::where($where);
        $sqls = [
            ["UPDATE `{$table}`"],
            ["SET {$dataSql}", 'params' => $dataParams],
            ["WHERE {$whereSql}", 'params' => $whereParams],
        ];
        $this->conn->raw();
        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return ConnectionInterface
     */
    public function update(string $field, $value): ConnectionInterface
    {

    }

    /**
     * @return ConnectionInterface
     */
    public function delete(): ConnectionInterface
    {

    }

}
