<?php

namespace Mix\Database;

/**
 * Trait QueryBuilder
 * @package Mix\Database
 */
trait QueryBuilder
{

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
     * @param string $table
     * @return $this
     */
    public function table(string $table): ConnectionInterface
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param string ...$fields
     * @return $this
     */
    public function select(string ...$fields): ConnectionInterface
    {
        $this->select = array_merge($this->select, $fields);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @param ...$values
     * @return $this
     */
    public function join(string $table, string $on, ...$values): ConnectionInterface
    {
        array_push($this->join, ['INNER JOIN', $table, $on, $values]);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @param ...$values
     * @return $this
     */
    public function leftJoin(string $table, string $on, ...$values): ConnectionInterface
    {
        array_push($this->join, ['LEFT JOIN', $table, $on, $values]);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @param ...$values
     * @return $this
     */
    public function rightJoin(string $table, string $on, ...$values): ConnectionInterface
    {
        array_push($this->join, ['RIGHT JOIN', $table, $on, $values]);
        return $this;
    }

    /**
     * @param string $table
     * @param string $on
     * @param ...$values
     * @return $this
     */
    public function fullJoin(string $table, string $on, ...$values): ConnectionInterface
    {
        array_push($this->join, ['FULL JOIN', $table, $on, $values]);
        return $this;
    }

    /**
     * @param string $expr
     * @param ...$values
     * @return $this
     */
    public function where(string $expr, ...$values): ConnectionInterface
    {
        array_push($this->where, ['AND', $expr, $values]);
        return $this;
    }

    /**
     * @param string $expr
     * @param ...$values
     * @return $this
     */
    public function or(string $expr, ...$values): ConnectionInterface
    {
        array_push($this->where, ['OR', $expr, $values]);
        return $this;
    }

    /**
     * @param string $field
     * @param string $order
     * @return $this
     */
    public function order(string $field, string $order): ConnectionInterface
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
    public function group(string ...$fields): ConnectionInterface
    {
        $this->group = array_merge($this->group, $fields);
        return $this;
    }

    /**
     * @param string $expr
     * @param ...$values
     * @return $this
     */
    public function having(string $expr, ...$values): ConnectionInterface
    {
        array_push($this->having, [$expr, $values]);
        return $this;
    }

    /**
     * offset
     * @param int $length
     * @return $this
     */
    public function offset(int $length): ConnectionInterface
    {
        $this->offset = $length;
        return $this;
    }

    /**
     * limit
     * @param int $length
     * @return $this
     */
    public function limit(int $length): ConnectionInterface
    {
        $this->limit = $length;
        return $this;
    }

    /**
     * 意向排它锁
     * @return $this
     */
    public function lockForUpdate(): ConnectionInterface
    {
        $this->lock = 'FOR UPDATE';
        return $this;
    }

    /**
     * 意向共享锁
     * @return $this
     */
    public function sharedLock(): ConnectionInterface
    {
        $this->lock = 'LOCK IN SHARE MODE';
        return $this;
    }

    /**
     * @param string $index
     * @param array $data
     * @return array
     */
    protected function build(string $index, array $data = []): array
    {
        $sqls = $values = [];

        // select
        if ($index == 'SELECT') {
            if ($this->select) {
                $select = implode(', ', $this->select);
                $sqls[] = "SELECT {$select}";
            } else {
                $sqls[] = "SELECT *";
            }
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
                        array_push($set, "$k = {$v->__toString()}");
                    } else {
                        $set[] = "$k = ?";
                        array_push($values, $v);
                    }
                }
                $sqls[] = "UPDATE {$this->table} SET " . implode(', ', $set);
            } else {
                $sqls[] = "FROM {$this->table}";
            }
        }

        // join
        if ($this->join) {
            foreach ($this->join as $item) {
                list($keyword, $table, $on, $vals) = $item;
                $sqls[] = "{$keyword} {$table} ON {$on}";
                array_push($values, ...$vals);
            }
        }

        // where
        if ($this->where) {
            $sqls[] = "WHERE";
            foreach ($this->where as $key => $item) {
                list($keyword, $expr, $vals) = $item;

                // in 处理
                foreach ($vals as $k => $val) {
                    if (is_array($val)) {
                        foreach ($val as &$value) {
                            if (is_string($value)) {
                                $value = "'$value'";
                            }
                        }
                        $expr = preg_replace('/\?/', implode(',', $val), $expr, 1);
                        unset($vals[$k]);
                    }
                }

                if ($key == 0) {
                    $sqls[] = "{$expr}";
                } else {
                    $sqls[] = "{$keyword} {$expr}";
                }
                array_push($values, ...$vals);
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
                list($expr, $vals) = $item;
                $subSql[] = "$expr";
                array_push($values, ...$vals);
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

        // clear
        $this->table = '';
        $this->select = [];
        $this->join = [];
        $this->where = [];
        $this->order = [];
        $this->group = [];
        $this->having = [];
        $this->offset = 0;
        $this->limit = 0;
        $this->lock = '';

        // 聚合
        return [implode(' ', $sqls), $values];
    }

}
