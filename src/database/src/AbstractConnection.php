<?php

namespace Mix\Database;

/**
 * Class AbstractConnection
 * @package Mix\Database
 */
abstract class AbstractConnection implements ConnectionInterface
{
    use QueryBuilder;

    /**
     * 驱动
     * @var Driver
     */
    protected $driver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Closure
     */
    protected $debug;

    /**
     * PDOStatement
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * sql
     * @var string
     */
    protected $sql = '';

    /**
     * params
     * @var array
     */
    protected $params = [];

    /**
     * values
     * @var array
     */
    protected $values = [];

    /**
     * 查询数据
     * @var array [$sql, $params, $values, $time]
     */
    protected $sqlData = [];

    /**
     * 归还连接前缓存处理
     * @var array
     */
    protected $options = [];

    /**
     * 归还连接前缓存处理
     * @var string
     */
    protected $lastInsertId;

    /**
     * 归还连接前缓存处理
     * @var int
     */
    protected $rowCount;

    /**
     * 因为协程模式下每次执行完，Driver 会被回收，因此不允许复用 Connection，必须每次都从 Database->borrow()
     * 为了保持与同步模式的兼容性，因此限制 Connection 不可多次执行
     * 事务在 commit rollback __destruct 之前可以多次执行
     * @var bool
     */
    protected $executed = false;

    /**
     * AbstractConnection constructor.
     * @param Driver $driver
     * @param LoggerInterface|null $logger
     */
    public function __construct(Driver $driver, ?LoggerInterface $logger)
    {
        $this->driver = $driver;
        $this->logger = $logger;
        $this->options = $driver->options();
    }

    /**
     * 连接
     * @throws \PDOException
     */
    public function connect(): void
    {
        $this->driver->connect();
    }

    /**
     * 关闭连接
     */
    public function close(): void
    {
        $this->statement = null;
        $this->driver->close();
    }

    /**
     * 重新连接
     * @throws \PDOException
     */
    protected function reconnect(): void
    {
        $this->close();
        $this->connect();
    }

    /**
     * 判断是否为断开连接异常
     * @param \Throwable $ex
     * @return bool
     */
    protected static function isDisconnectException(\Throwable $ex)
    {
        $disconnectMessages = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'failed with errno',
        ];
        $errorMessage = $ex->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $sql
     * @param ...$values
     * @return ConnectionInterface
     */
    public function raw(string $sql, ...$values): ConnectionInterface
    {
        // 保存SQL
        $this->sql = $sql;
        $this->values = $values;
        $this->sqlData = [$this->sql, $this->params, $this->values, 0];

        // 执行
        return $this->execute();
    }

    /**
     * @param string $sql
     * @param ...$values
     * @return ConnectionInterface
     */
    public function exec(string $sql, ...$values): ConnectionInterface
    {
        return $this->raw($sql, ...$values);
    }

    /**
     * @return ConnectionInterface
     * @throws \Throwable
     */
    public function execute(): ConnectionInterface
    {
        if ($this->executed) {
            throw new \RuntimeException('The Connection::class cannot be executed repeatedly, please use the Database::class call');
        }

        $beginTime = microtime(true);
        try {
            $this->prepare();
            $success = $this->statement->execute();
            if (!$success) {
                list($flag, $code, $message) = $this->statement->errorInfo();
                throw new \PDOException(sprintf('%s %d %s', $flag, $code, $message), $code);
            }
        } catch (\Throwable $ex) {
            throw $ex;
        } finally {
            // 只可执行一次
            // 事务除外，事务在 commit rollback __destruct 中处理
            if (!$this instanceof Transaction) {
                $this->executed = true;
            }

            // 记录执行时间
            $time = round((microtime(true) - $beginTime) * 1000, 2);
            $this->sqlData[3] = $time;

            // 缓存常用数据，让资源可以提前回收
            // 包含 pool 并且在非事务的情况下才执行缓存，并且提前归还连接到池，提高并发性能并且降低死锁概率
            isset($this->lastInsertId) and $this->lastInsertId = null;
            isset($this->rowCount) and $this->rowCount = null;
            if (isset($ex)) {
                // 有异常: 使用默认值, 不调用 driver, statement
                $this->lastInsertId = '';
                $this->rowCount = 0;
            } elseif ($this->driver->pool && !$this instanceof Transaction) {
                // 有pool: 提前缓存 lastInsertId, rowCount 让连接提前归还
                try {
                    if (stripos($this->sql, 'INSERT INTO') !== false) {
                        $this->lastInsertId = $this->driver->instance()->lastInsertId();
                    } else {
                        $this->lastInsertId = '';
                    }
                } catch (\Throwable $ex) {
                    // pgsql: SQLSTATE[55000]: Object not in prerequisite state: 7 ERROR:  lastval is not yet defined in this session
                    $this->lastInsertId = '';
                }
                $this->rowCount = $this->statement->rowCount();
            }

            // logger
            if ($this->logger) {
                $log = $this->queryLog();
                $this->logger->trace(
                    $log['time'],
                    $log['sql'],
                    $log['bindings'],
                    $this->rowCount(),
                    $ex ?? null
                );
            }

            // debug
            $debug = $this->debug;
            $debug and $debug($this);
        }

        // 事务还是要复用 Connection 清理依然需要
        // 抛出异常时不清理，因为需要重连后重试
        $this->clear();

        // 执行完立即回收
        // 抛出异常时不回收，重连那里还需要验证是否在事务中
        // 事务除外，事务在 commit rollback __destruct 中回收
        if ($this->driver->pool && !$this instanceof Transaction) {
            $this->driver->__return();
            $this->driver = new EmptyDriver();
        }

        return $this;
    }

    /**
     * 事务还是要复用 Connection 清理依然需要
     */
    protected function clear()
    {
        $this->debug = null;
        $this->sql = '';
        $this->params = [];
        $this->values = [];
    }

    protected function prepare()
    {
        if (!empty($this->params)) { // 参数绑定
            // 支持insert里面带函数
            foreach ($this->params as $k => $v) {
                if ($v instanceof Expr) {
                    unset($this->params[$k]);
                    $k = substr($k, 0, 1) == ':' ? $k : ":{$k}";
                    $this->sql = str_replace($k, $v->__toString(), $this->sql);
                }
            }
            $statement = $this->driver->instance()->prepare($this->sql);
            if (!$statement) {
                throw new \PDOException('PDO prepare failed');
            }
            $this->statement = $statement;
            $this->sqlData = [$this->sql, $this->params, [], 0,]; // 必须在 bindParam 前，才能避免类型被转换
            foreach ($this->params as $key => &$value) {
                if (!$this->statement->bindParam($key, $value, static::bindType($value))) {
                    throw new \PDOException('PDOStatement bindParam failed');
                }
            }
        } elseif (!empty($this->values)) { // 值绑定
            $statement = $this->driver->instance()->prepare($this->sql);
            if (!$statement) {
                throw new \PDOException('PDO prepare failed');
            }
            $this->statement = $statement;
            $this->sqlData = [$this->sql, [], $this->values, 0];
            foreach ($this->values as $key => $value) {
                if (!$this->statement->bindValue($key + 1, $value, static::bindType($value))) {
                    throw new \PDOException('PDOStatement bindValue failed');
                }
            }
        } else { // 无参数
            $statement = $this->driver->instance()->prepare($this->sql);
            if (!$statement) {
                throw new \PDOException('PDO prepare failed');
            }
            $this->statement = $statement;
            $this->sqlData = [$this->sql, [], [], 0];
        }
    }

    /**
     * @param $value
     * @return int
     */
    protected static function bindType($value): int
    {
        switch (gettype($value)) {
            case 'boolean':
                $type = \PDO::PARAM_BOOL;
                break;
            case 'NULL':
                $type = \PDO::PARAM_NULL;
                break;
            case 'integer':
                $type = \PDO::PARAM_INT;
                break;
            default:
                $type = \PDO::PARAM_STR;
                break;
        }
        return $type;
    }

    /**
     * @param \Closure $func
     * @return $this
     */
    public function debug(\Closure $func): ConnectionInterface
    {
        $this->debug = $func;
        return $this;
    }

    /**
     * 返回多行
     * @return array
     */
    public function get(): array
    {
        if ($this->table) {
            list($sql, $values) = $this->build('SELECT');
            $this->raw($sql, ...$values);
        }
        return $this->queryAll();
    }

    /**
     * 返回一行
     * @return array|object|false
     */
    public function first()
    {
        if ($this->table) {
            list($sql, $values) = $this->build('SELECT');
            $this->raw($sql, ...$values);
        }
        return $this->queryOne();
    }

    /**
     * 返回单个值
     * @param string $field
     * @return mixed
     * @throws \PDOException
     */
    public function value(string $field)
    {
        if ($this->table) {
            list($sql, $values) = $this->build('SELECT');
            $this->raw($sql, ...$values);
        }
        $result = $this->queryOne();
        if (empty($result)) {
            throw new \PDOException(sprintf('Field %s not found', $field));
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
        list($sql, $values) = $this->build('UPDATE', $data);
        return $this->exec($sql, ...$values);
    }

    /**
     * @param string $field
     * @param $value
     * @return ConnectionInterface
     */
    public function update(string $field, $value): ConnectionInterface
    {
        list($sql, $values) = $this->build('UPDATE', [
            $field => $value
        ]);
        return $this->exec($sql, ...$values);
    }

    /**
     * @return ConnectionInterface
     */
    public function delete(): ConnectionInterface
    {
        list($sql, $values) = $this->build('DELETE');
        return $this->exec($sql, ...$values);
    }

    /**
     * 返回结果集
     * 注意：只能在 debug 闭包中使用，因为连接归还到池后，如果还有调用结果集会有一致性问题
     * @return \PDOStatement
     */
    public function statement(): \PDOStatement
    {
        // check debug
        if (!$this->debug) {
            throw new \RuntimeException('Can only be used in debug closure');
        }

        return $this->statement;
    }

    /**
     * 返回一行
     * @param int $fetchStyle
     * @return array|object|false
     */
    public function queryOne(int $fetchStyle = null)
    {
        $fetchStyle = $fetchStyle ?: $this->options[\PDO::ATTR_DEFAULT_FETCH_MODE];
        return $this->statement->fetch($fetchStyle);
    }

    /**
     * 返回多行
     * @param int $fetchStyle
     * @return array
     */
    public function queryAll(int $fetchStyle = null): array
    {
        $fetchStyle = $fetchStyle ?: $this->options[\PDO::ATTR_DEFAULT_FETCH_MODE];
        return $this->statement->fetchAll($fetchStyle);
    }

    /**
     * 返回一列 (默认第一列)
     * @param int $columnNumber
     * @return array
     */
    public function queryColumn(int $columnNumber = 0): array
    {
        $column = [];
        while ($row = $this->statement->fetchColumn($columnNumber)) {
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
        return $this->statement->fetchColumn();
    }

    /**
     * 返回最后插入行的ID或序列值
     * @return string
     */
    public function lastInsertId(): string
    {
        if (!isset($this->lastInsertId) && $this->driver instanceof Driver) {
            $this->lastInsertId = $this->driver->instance()->lastInsertId();
        }
        return $this->lastInsertId;
    }

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return int
     */
    public function rowCount(): int
    {
        if (!isset($this->rowCount) && $this->driver instanceof Driver) {
            $this->rowCount = $this->statement->rowCount();
        }
        return $this->rowCount;
    }

    /**
     * 获取查询日志
     * @return array
     */
    public function queryLog(): array
    {
        $sql = '';
        $params = $values = [];
        $time = 0;
        !empty($this->sqlData) and list($sql, $params, $values, $time) = $this->sqlData;
        return [
            'time' => $time,
            'sql' => $sql,
            'bindings' => $values ?: $params,
        ];
    }

    /**
     * @param string $table
     * @param array $data
     * @param string $insert
     * @return ConnectionInterface
     */
    public function insert(string $table, array $data, string $insert = 'INSERT INTO'): ConnectionInterface
    {
        $keys = array_keys($data);
        $fields = array_map(function ($key) {
            return ":{$key}";
        }, $keys);
        $sql = "{$insert} `{$table}` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $fields) . ")";
        $this->params = array_merge($this->params, $data);
        return $this->exec($sql);
    }

    /**
     * @param string $table
     * @param array $data
     * @param string $insert
     * @return ConnectionInterface
     */
    public function batchInsert(string $table, array $data, string $insert = 'INSERT INTO'): ConnectionInterface
    {
        $keys = array_keys($data[0]);
        $sql = "{$insert} `{$table}` (`" . implode('`, `', $keys) . "`) VALUES ";
        $values = [];
        $subSql = [];
        foreach ($data as $item) {
            $placeholder = [];
            foreach ($keys as $key) {
                $value = $item[$key];
                // 原始方法
                if ($value instanceof Expr) {
                    $placeholder[] = $value->__toString();
                    continue;
                }
                $values[] = $value;
                $placeholder[] = '?';
            }
            $subSql[] = "(" . implode(', ', $placeholder) . ")";
        }
        $sql .= implode(', ', $subSql);
        return $this->exec($sql, ...$values);
    }

    /**
     * 返回当前PDO连接是否在事务内（在事务内的连接回池会造成下次开启事务产生错误）
     * @return bool
     */
    public function inTransaction(): bool
    {
        $pdo = $this->driver->instance();
        return (bool)($pdo ? $pdo->inTransaction() : false);
    }

    /**
     * 自动事务
     * @param \Closure $closure
     * @throws \Throwable
     */
    public function transaction(\Closure $closure)
    {
        $tx = $this->beginTransaction();
        try {
            call_user_func($closure, $tx);
            $tx->commit();
        } catch (\Throwable $ex) {
            $tx->rollback();
            throw $ex;
        }
    }

    /**
     * @return Transaction
     * @throws \PDOException
     */
    public function beginTransaction(): Transaction
    {
        $driver = $this->driver;
        $this->driver = null; // 使其在析构时不回收
        return new Transaction($driver, $this->logger);
    }
}
