<?php

namespace Mix\Database;

use Mix\Bean\BeanInjector;
use Mix\Database\Event\ExecutedEvent;
use Mix\Database\Helper\WhereHelper;
use Mix\Database\Helper\BuildHelper;
use Mix\Database\Query\Expression;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Connection
 * @package Mix\Database
 * @author liu,jian <coder.keda@gmail.com>
 */
abstract class AbstractConnection
{

    /**
     * 数据源格式
     * @var string
     */
    public $dsn = '';

    /**
     * 数据库用户名
     * @var string
     */
    public $username = 'root';

    /*
     * 数据库密码
     */
    public $password = '';

    /**
     * 驱动连接选项
     * @var array
     */
    public $attributes = [];

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $eventDispatcher;

    /**
     * PDO
     * @var \PDO
     */
    protected $_pdo;

    /**
     * PDOStatement
     * @var \PDOStatement
     */
    protected $_pdoStatement;

    /**
     * sql
     * @var string
     */
    protected $_sql = '';

    /**
     * params
     * @var array
     */
    protected $_params = [];

    /**
     * values
     * @var array
     */
    protected $_values = [];

    /**
     * 查询数据
     * @var array
     */
    protected $_queryData = [];

    /**
     * 默认驱动连接选项
     * @var array
     */
    protected $_defaultAttributes = [
        \PDO::ATTR_EMULATE_PREPARES   => false,
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];

    /**
     * 驱动连接选项
     * @var array
     */
    protected $_attributes = [];

    /**
     * AbstractConnection constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 驱动连接选项
     * @return array
     */
    protected function getAttributes()
    {
        return $this->attributes + $this->_defaultAttributes;
    }

    /**
     * 创建连接
     * @return \PDO
     */
    protected function createConnection()
    {
        return new \PDO(
            $this->dsn,
            $this->username,
            $this->password,
            $this->getAttributes()
        );
    }

    /**
     * 连接
     * @return bool
     */
    public function connect()
    {
        $this->_pdo = $this->createConnection();
        return true;
    }

    /**
     * 关闭连接
     * @return bool
     */
    public function close()
    {
        $this->_pdoStatement = null;
        $this->_pdo          = null;
        return true;
    }

    /**
     * 构建查询片段
     * @param array $item
     * @return string|bool
     */
    protected function buildQueryFragment(array $item)
    {
        if (isset($item['if']) && $item['if'] == false) {
            return false;
        }
        if (isset($item['params'])) {
            $this->bindParams($item['params']);
        }
        return array_shift($item);
    }

    /**
     * 准备执行语句
     * @param $sql
     * @return $this
     */
    public function prepare($sql)
    {
        // 清扫数据
        $this->_sql    = '';
        $this->_params = [];
        $this->_values = [];
        // 字符串构建
        if (is_string($sql)) {
            $this->_sql = $sql;
        }
        // 数组构建
        if (is_array($sql)) {
            $fragments = [];
            foreach ($sql as $item) {
                $fragment = $this->buildQueryFragment($item);
                if ($fragment) {
                    $fragments[] = $fragment;
                }
            }
            $this->_sql = implode(' ', $fragments);
        }
        // 保存SQL
        $this->_queryData = [$this->_sql, [], [], 0];
        // 返回
        return $this;
    }

    /**
     * 绑定参数
     * @param array $data
     * @return $this
     */
    public function bindParams(array $data)
    {
        $this->_params = array_merge($this->_params, $data);
        return $this;
    }

    /**
     * 绑定值
     * @param array $data
     * @return $this
     */
    protected function bindValues(array $data)
    {
        $this->_values = array_merge($this->_values, $data);
        return $this;
    }

    /**
     * 返回当前PDO连接是否在事务内（在事务内的连接回池会造成下次开启事务产生错误）
     * @return bool
     */
    public function inTransaction()
    {
        /** @var  $pdo \PDO */
        $pdo = $this->_pdo;
        return (bool)($pdo ? $pdo->inTransaction() : false);
    }

    /**
     * 返回一个RawQuery对象，对象的值将不经过参数绑定，直接解释为SQL的一部分，适合传递数据库原生函数
     * @param string $value
     * @return Expression
     */
    public static function raw(string $value)
    {
        return new Expression($value);
    }

    /**
     * 绑定数组参数
     * @param $sql
     * @param $params
     * @return array
     */
    protected function bindArrayParams($sql, $params)
    {
        foreach ($params as $key => $item) {
            if (is_array($item)) {
                unset($params[$key]);
                $key = substr($key, 0, 1) == ':' ? $key : ":{$key}";
                $sql = str_replace($key, implode(', ', $this->quotes($item)), $sql);
            }
        }
        return [$sql, $params];
    }

    /**
     * 构建查询
     */
    protected function build()
    {
        if (!empty($this->_params)) {
            // 准备与参数绑定
            // 原始方法
            foreach ($this->_params as $key => $item) {
                if ($item instanceof Expression) {
                    unset($this->_params[$key]);
                    $key        = substr($key, 0, 1) == ':' ? $key : ":{$key}";
                    $this->_sql = str_replace($key, $item->getValue(), $this->_sql);
                }
            }
            // 有参数
            list($sql, $params) = $this->bindArrayParams($this->_sql, $this->_params);
            $this->_pdoStatement = $this->_pdo->prepare($sql);
            $this->_queryData    = [$sql, $params, [], 0]; // 必须在 bindParam 前，才能避免类型被转换
            foreach ($params as $key => &$value) {
                $this->_pdoStatement->bindParam($key, $value);
            }
        } elseif (!empty($this->_values)) {
            // 批量插入
            $this->_pdoStatement = $this->_pdo->prepare($this->_sql);
            $this->_queryData    = [$this->_sql, [], $this->_values, 0];
            foreach ($this->_values as $key => $value) {
                $this->_pdoStatement->bindValue($key + 1, $value);
            }
        } else {
            // 无参数
            $this->_pdoStatement = $this->_pdo->prepare($this->_sql);
            $this->_queryData    = [$this->_sql, [], [], 0];
        }
    }

    /**
     * 清扫构建查询数据
     */
    protected function clear()
    {
        $this->_sql    = '';
        $this->_params = [];
        $this->_values = [];
    }

    /**
     * 调度事件
     */
    protected function dispatchEvent()
    {
        if (!$this->eventDispatcher) {
            return;
        }
        $log             = $this->getLastLog();
        $event           = new ExecutedEvent();
        $event->sql      = $log['sql'];
        $event->bindings = $log['bindings'];
        $event->time     = $log['time'];
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * 获取微秒时间
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * 执行SQL语句
     * @return bool
     */
    public function execute()
    {
        // 构建查询
        $this->build();
        // 执行
        $microtime           = static::microtime();
        $success             = $this->_pdoStatement->execute();
        $time                = round((static::microtime() - $microtime) * 1000, 2);
        $this->_queryData[3] = $time;
        // 清扫
        $this->clear();
        // 调度执行事件
        $this->dispatchEvent();
        // 返回
        return $success;
    }

    /**
     * 返回结果集
     * @return \PDOStatement
     */
    public function query()
    {
        $this->execute();
        return $this->_pdoStatement;
    }

    /**
     * 返回一行
     * @param int $fetchStyle
     * @return array|object
     */
    public function queryOne(int $fetchStyle = null)
    {
        $this->execute();
        $fetchStyle = $fetchStyle ?: $this->getAttributes()[\PDO::ATTR_DEFAULT_FETCH_MODE];
        return $this->_pdoStatement->fetch($fetchStyle);
    }

    /**
     * 返回多行
     * @param int $fetchStyle
     * @return array
     */
    public function queryAll(int $fetchStyle = null)
    {
        $this->execute();
        $fetchStyle = $fetchStyle ?: $this->getAttributes()[\PDO::ATTR_DEFAULT_FETCH_MODE];
        return $this->_pdoStatement->fetchAll($fetchStyle);
    }

    /**
     * 返回一列 (默认第一列)
     * @param int $columnNumber
     * @return array
     */
    public function queryColumn(int $columnNumber = 0)
    {
        $this->execute();
        $column = [];
        while ($row = $this->_pdoStatement->fetchColumn($columnNumber)) {
            $column[] = $row;
        }
        return $column;
    }

    /**
     * 返回一个标量值
     * @return mixed
     */
    public function queryScalar()
    {
        $this->execute();
        return $this->_pdoStatement->fetchColumn();
    }

    /**
     * 返回最后插入行的ID或序列值
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->_pdo->lastInsertId();
    }

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return int
     */
    public function getRowCount()
    {
        return $this->_pdoStatement->rowCount();
    }

    /**
     * 返回最后的SQL语句
     * @return string
     */
    public function getLastSql()
    {
        $sql    = '';
        $params = $values = [];
        !empty($this->_queryData) and list($sql, $params, $values) = $this->_queryData;
        if (empty($params) && empty($values)) {
            return $sql;
        }
        $values = $this->quotes($values);
        $params = $this->quotes($params);
        // 先处理 values，避免 params 中包含 ? 号污染结果
        $sql = vsprintf(str_replace('?', '%s', $sql), $values);
        // 处理 params
        foreach ($params as $key => $value) {
            $key = substr($key, 0, 1) == ':' ? $key : ":{$key}";
            $sql = str_replace($key, $value, $sql);
        }
        return $sql;
    }

    /**
     * 获取最后的日志
     * @return array
     */
    public function getLastLog()
    {
        $sql    = '';
        $params = $values = [];
        $time   = 0;
        !empty($this->_queryData) and list($sql, $params, $values, $time) = $this->_queryData;
        return [
            'sql'      => $sql,
            'bindings' => $values ?: $params,
            'time'     => $time,
        ];
    }

    /**
     * 给字符串加单引号
     * @param $var
     * @return array|string
     */
    protected function quotes($var)
    {
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $var[$k] = $this->quotes($v);
            }
            return $var;
        }
        return is_string($var) ? $this->_pdo->quote($var) : $var;
    }

    /**
     * 插入
     * @param string $table
     * @param array $data
     * @return $this
     */
    public function insert(string $table, array $data)
    {
        $keys   = array_keys($data);
        $fields = array_map(function ($key) {
            return ":{$key}";
        }, $keys);
        $sql    = "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $fields) . ")";
        $this->prepare($sql);
        $this->bindParams($data);
        return $this;
    }

    /**
     * 批量插入
     * @param string $table
     * @param array $data
     * @return $this
     */
    public function batchInsert(string $table, array $data)
    {
        $keys   = array_keys($data[0]);
        $sql    = "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES ";
        $values = [];
        $subSql = [];
        foreach ($data as $item) {
            $placeholder = [];
            foreach ($keys as $key) {
                $value = $item[$key];
                // 原始方法
                if ($value instanceof Expression) {
                    $placeholder[] = $value->getValue();
                    continue;
                }
                $values[]      = $value;
                $placeholder[] = '?';
            }
            $subSql[] = "(" . implode(', ', $placeholder) . ")";
        }
        $sql .= implode(', ', $subSql);
        $this->prepare($sql);
        $this->bindValues($values);
        return $this;
    }

    /**
     * 更新
     * @param string $table
     * @param array $data
     * @param array $where
     * @return $this
     */
    public function update(string $table, array $data, array $where)
    {
        if (!WhereHelper::isMulti($where)) {
            $where = [$where];
        }
        list($dataSql, $dataParams) = BuildHelper::data($data);
        list($whereSql, $whereParams) = BuildHelper::where($where);
        $this->prepare([
            ["UPDATE `{$table}`"],
            ["SET {$dataSql}", 'params' => $dataParams],
            ["WHERE {$whereSql}", 'params' => $whereParams],
        ]);
        return $this;
    }

    /**
     * 删除
     * @param string $table
     * @param array $where
     * @return $this
     */
    public function delete(string $table, array $where)
    {
        if (!WhereHelper::isMulti($where)) {
            $where = [$where];
        }
        list($sql, $params) = BuildHelper::where($where);
        $this->prepare([
            ["DELETE FROM `{$table}`"],
            ["WHERE {$sql}", 'params' => $params],
        ]);
        return $this;
    }

    /**
     * 自动事务
     * @param \Closure $closure
     * @throws \Throwable
     */
    public function transaction(\Closure $closure)
    {
        $this->beginTransaction();
        try {
            $closure();
            // 提交事务
            $this->commit();
        } catch (\Throwable $e) {
            // 回滚事务
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * 开始事务
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->_pdo->beginTransaction();
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commit()
    {
        return $this->_pdo->commit();
    }

    /**
     * 回滚事务
     * @return bool
     */
    public function rollback()
    {
        return $this->_pdo->rollBack();
    }

    /**
     * 启动查询生成器
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table)
    {
        return QueryBuilder::new($this)->table($table);
    }

}
