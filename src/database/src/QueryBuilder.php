<?php

namespace Mix\Database;

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
     * @var \Closure
     */
    protected $debugFunc;

    /**
     * @var array
     */
    protected $update;

    /**
     * @var bool
     */
    protected $delete;

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
     * @param \Closure $func
     * @return $this
     */
    public function debug(\Closure $func): QueryBuilder
    {
        $this->debugFunc = $func;
        return $this;
    }

    /**
     * @param string $index
     * @param array $data
     * @return ConnectionInterface
     */
    protected function raw(string $index, array $data = []): ConnectionInterface
    {
        $sqls = $values = [];

        // select
        if ($this->select) {
            $select = implode(', ', $this->select);
            $sqls[] = "SELECT {$select}";
        } else {
            $sqls[] = "SELECT *";
        }

        // delete
        if ($index == 'DELETE') {
            $sqls[] = "DELETE";
        }

        // table
        if ($this->table) {
            // update
            if ($index == 'UPDATE') {
                $set = [];
                foreach ($data as $k => $v) {
                    if ($v instanceof Expr) {
                        array_push($set, $v->__toString());
                    } else {
                        $set[] = "$k = ?";
                        array_push($values, $v);
                    }
                }
                $sqls[] = "UPDATE SET " . implode(', ', $set) . " FROM {$this->table}";
            } else {
                $sqls[] = "FROM {$this->table}";
            }
        }

        // join
        if ($this->join) {
            foreach ($this->join as $item) {
                list($keyword, $table, $on, $args) = $item;
                $sqls[] = "{$keyword} {$table} ON {$on}";
                array_push($values, ...$args);
            }
        }

        // where
        if ($this->where) {
            $sqls[] = "WHERE";
            foreach ($this->where as $key => $item) {
                list($keyword, $expr, $args) = $item;

                // in 处理


                if ($key == 0) {
                    $sqls[] = "{$expr}";
                } else {
                    $sqls[] = "{$keyword} {$expr}";
                }
                array_push($values, ...$args);
            }
        }

        // group
        if ($this->group) {
            $sqls[] = "GROUP BY " . implode(', ', $this->group);
        }

        // having
        if ($this->having) {
            $subSql = [];
            foreach ($this->having as $item) {
                list($expr, $args) = $item;
                $subSql[] = "$expr";
                array_push($values, ...$args);
            }
            $subSql = count($subSql) == 1 ? array_pop($subSql) : implode(' AND ', $subSql);
            $sqls[] = "HAVING {$subSql}";
        }

        // order
        if ($this->order) {
            $subSql = [];
            foreach ($this->order as $item) {
                list($field, $order) = $item;
                $subSql[] = "{$field} {$order}";
            }
            $sqls[] = "ORDER BY " . implode(', ', $subSql);
        }

        // limit and offset
        if ($this->limit > 0) {
            $sqls[] = 'LIMIT ?, ?';
            array_push($values, $this->offset, $this->limit);
        }

        // lock
        if ($this->lock) {
            $sqls[] = $this->lock;
        }

        // 聚合
        $sql = implode(' ', $sqls);

        // debug & 执行
        try {
            $conn = $this->conn->raw($sql, ...$values);
        } catch (\Throwable $ex) {
            throw $ex;
        } finally {
            $func = $this->debugFunc;
            $func and $func($this->conn);
        }

        return $conn;
    }

    /**
     * 返回多行
     * @return array
     */
    public function get()
    {
        return $this->raw('SELECT')->queryAll();
    }

    /**
     * 返回一行
     * @return mixed
     */
    public function first()
    {
        return $this->raw('SELECT')->queryOne();
    }

    /**
     * 返回单个值
     * @param string $field
     * @return mixed
     * @throws \PDOException
     */
    public function value(string $field)
    {
        $result = $this->raw('SELECT')->queryOne();
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
     * @param array $data
     * @return ConnectionInterface
     */
    public function updates(array $data): ConnectionInterface
    {
        return $this->raw('UPDATE', $data);
    }

    /**
     * @param string $field
     * @param $value
     * @return ConnectionInterface
     */
    public function update(string $field, $value): ConnectionInterface
    {
        return $this->raw('UPDATE', [
            $field => $value
        ]);
    }

    /**
     * @return ConnectionInterface
     */
    public function delete(): ConnectionInterface
    {
        $this->delete = true;
        return $this->raw('DELETE');
    }

}
