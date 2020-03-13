<?php

namespace Mix\Database;

use Mix\Database\Helper\BuildHelper;

/**
 * Class QueryBuilder
 * @package Mix\Database
 * @author liu,jian <coder.keda@gmail.com>
 */
class QueryBuilder
{

    /**
     * 连接
     * @var ConnectionInterface
     */
    public $connection;

    /**
     * @var string
     */
    protected $_table = '';

    /**
     * @var array
     */
    protected $_select = [];

    /**
     * @var array
     */
    protected $_join = [];

    /**
     * @var array
     */
    protected $_where = [];

    /**
     * @var array
     */
    protected $_orderBy = [];

    /**
     * @var array
     */
    protected $_groupBy = [];

    /**
     * @var array
     */
    protected $_having = [];

    /**
     * @var int
     */
    protected $_offset = 0;

    /**
     * @var int
     */
    protected $_limit = 0;

    /**
     * 使用静态方法创建实例
     * @param ConnectionInterface $connection
     * @return QueryBuilder
     */
    public static function new(ConnectionInterface $connection)
    {
        return new static($connection);
    }

    /**
     * QueryBuilder constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * table
     * @param string $table
     * @return $this
     */
    public function table(string $table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * select
     * @param mixed ...$fields
     * @return $this
     */
    public function select(...$fields)
    {
        $this->_select = array_merge($this->_select, $fields);
        return $this;
    }

    /**
     * join
     * @param string $table
     * @param array $on
     * @return $this
     */
    public function join(string $table, array $on)
    {
        array_push($this->_join, ['INNER JOIN', $table, $on]);
        return $this;
    }

    /**
     * leftJoin
     * @param string $table
     * @param array $on
     * @return $this
     */
    public function leftJoin(string $table, array $on)
    {
        array_push($this->_join, ['LEFT JOIN', $table, $on]);
        return $this;
    }

    /**
     * rightJoin
     * @param string $table
     * @param array $on
     * @return $this
     */
    public function rightJoin(string $table, array $on)
    {
        array_push($this->_join, ['RIGHT JOIN', $table, $on]);
        return $this;
    }

    /**
     * fullJoin
     * @param string $table
     * @param array $on
     * @return $this
     */
    public function fullJoin(string $table, array $on)
    {
        array_push($this->_join, ['FULL JOIN', $table, $on]);
        return $this;
    }

    /**
     * where
     * @param array $where
     * @return $this
     */
    public function where(array $where)
    {
        if (!BuildHelper::isMulti($where)) {
            array_push($this->_where, $where);
        } else {
            $this->_where = array_merge($this->_where, $where);
        }
        return $this;
    }

    /**
     * orderBy
     * @param string $field
     * @param string $order
     * @return $this
     */
    public function orderBy(string $field, string $order)
    {
        if (!in_array($order, ['asc', 'desc'])) {
            throw new \RuntimeException('Sort can only be asc or desc.');
        }
        array_push($this->_orderBy, [$field, strtoupper($order)]);
        return $this;
    }

    /**
     * groupBy
     * @param mixed ...$fields
     * @return $this
     */
    public function groupBy(...$fields)
    {
        $this->_groupBy = array_merge($this->_groupBy, $fields);
        return $this;
    }

    /**
     * having
     * @param $field
     * @param $operator
     * @param $condition
     * @return $this
     */
    public function having($field, $operator, $condition)
    {
        array_push($this->_having, [$field, $operator, $condition]);
        return $this;
    }

    /**
     * offset
     * @param int $length
     * @return $this
     */
    public function offset(int $length)
    {
        $this->_offset = $length;
        return $this;
    }

    /**
     * limit
     * @param int $length
     * @return $this
     */
    public function limit(int $length)
    {
        $this->_limit = $length;
        return $this;
    }

    /**
     * 预处理
     * @return ConnectionInterface
     */
    public function prepare()
    {
        $sql = [];
        // select
        if ($this->_select) {
            $select = implode(', ', $this->_select);
            $sql[]  = ["SELECT {$select}"];
        } else {
            $sql[] = ["SELECT *"];
        }
        // table
        if ($this->_table) {
            $sql[] = ["FROM {$this->_table}"];
        }
        if ($this->_join) {
            foreach ($this->_join as $item) {
                list($type, $table, $on) = $item;
                $condition = BuildHelper::joinOn($on);
                $sql[]     = ["{$type} {$table} ON {$condition}"];
            }
        }
        // where
        if ($this->_where) {
            list($subSql, $subParams) = BuildHelper::where($this->_where);
            $sql[] = ["WHERE {$subSql}", 'params' => $subParams];
        }
        // groupBy
        if ($this->_groupBy) {
            $sql[] = ["GROUP BY " . implode(', ', $this->_groupBy)];
        }
        // having
        if ($this->_having) {
            $subSql = [];
            foreach ($this->_having as $item) {
                list($field, $operator, $condition) = $item;
                $subSql[] = "{$field} {$operator} {$condition}";
            }
            $subSql = count($subSql) == 1 ? array_pop($subSql) : implode(' AND ', $subSql);
            $sql[]  = ["HAVING {$subSql}"];
        }
        // orderBy
        if ($this->_orderBy) {
            $subSql = [];
            foreach ($this->_orderBy as $item) {
                list($field, $order) = $item;
                $subSql[] = "{$field} {$order}";
            }
            $sql[] = ["ORDER BY " . implode(', ', $subSql)];
        }
        // limit and offset
        if ($this->_limit > 0) {
            $sql[] = ['LIMIT :__offset, :__limit', 'params' => ['__offset' => $this->_offset, '__limit' => $this->_limit]];
        }
        // 返回
        return $this->connection->prepare($sql);
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

}
