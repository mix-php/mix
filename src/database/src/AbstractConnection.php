<?php

namespace Mix\Database;

/**
 * Class AbstractConnection
 * @package Mix\Database
 */
abstract class AbstractConnection implements ConnectionInterface
{

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
     * PDOStatement
     * @var \PDOStatement
     */
    public $statement;

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
     * 回收数据
     * @var array [$sql, $params, $values]
     */
    protected $recycleData = [];

    /**
     * 查询数据
     * @var array [$sql, $params, $values, $time, $lastInsertId]
     */
    protected $sqlData = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * AbstractConnection constructor.
     * @param Driver $driver
     * @param LoggerInterface|null $logger
     */
    public function __construct(Driver $driver, ?LoggerInterface $logger)
    {
        $this->driver = $driver;
        $this->options = $driver->options();
        $this->logger = $logger;
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
        $this->recycle();
    }

    /**
     * 判断是否为断开连接异常
     * @param \Throwable $e
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
        // 清扫数据
        $this->sql = '';
        $this->params = [];
        $this->values = [];

        // 保存SQL
        $this->sql = $sql;
        $this->values = $values;
        $this->sqlData = [$this->sql, [], $values, 0, ''];

        // 执行
        return $this->exec();
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
     * 构建查询
     * @throws \PDOException
     */
    protected function prepare()
    {
        if (!empty($this->params)) { // 参数绑定
            // 支持 insert 里面带函数
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
            $this->sqlData = [$this->sql, $this->params, [], 0, '']; // 必须在 bindParam 前，才能避免类型被转换
            foreach ($this->params as $key => &$value) {
                if (!$this->statement->bindParam($key, $value)) {
                    throw new \PDOException('PDOStatement bindParam failed');
                }
            }
        } elseif (!empty($this->values)) { // 值绑定
            $statement = $this->driver->instance()->prepare($this->sql);
            if (!$statement) {
                throw new \PDOException('PDO prepare failed');
            }
            $this->statement = $statement;
            $this->sqlData = [$this->sql, [], $this->values, 0, ''];
            foreach ($this->values as $key => $value) {
                if (!$this->statement->bindValue($key + 1, $value)) {
                    throw new \PDOException('PDOStatement bindValue failed');
                }
            }
        } else { // 无参数
            $statement = $this->driver->instance()->prepare($this->sql);
            if (!$statement) {
                throw new \PDOException('PDO prepare failed');
            }
            $this->statement = $statement;
            $this->sqlData = [$this->sql, [], [], 0, ''];
        }
    }

    /**
     * 清扫构建查询数据
     */
    protected function clear()
    {
        $this->recycleData = [$this->sql, $this->params, $this->values];
        $this->sql = '';
        $this->params = [];
        $this->values = [];
    }

    /**
     * 回收清扫的查询数据，用于重连后恢复查询
     */
    protected function recycle()
    {
        // beginTransaction 异常时没有数据
        if (empty($this->recycleData)) {
            return;
        }
        list($this->sql, $this->params, $this->values) = $this->recycleData;
    }

    /**
     * @return ConnectionInterface
     */
    public function exec(): ConnectionInterface
    {
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
            // 记录执行时间
            $time = round((microtime(true) - $beginTime) * 1000, 2);
            $this->sqlData[3] = $time;
            $this->sqlData[4] = $this->driver->instance()->lastInsertId();

            $this->clear();

            // print
            $log = $this->getQueryLog();
            $this->logger and $this->logger->trace(
                $time,
                $log['sql'],
                $log['bindings'],
                $this->getRowCount(),
                $ex ?? null
            );
        }

        // 执行完立即回收
        if ($this->driver->pool && get_called_class() != Transaction::class) {
            $this->driver->__return();
            $this->driver = new EmptyDriver();
        }

        return $this;
    }

    /**
     * 返回结果集
     * @return \PDOStatement
     */
    public function query(): \PDOStatement
    {
        return $this->statement;
    }

    /**
     * 返回一行
     * @param int $fetchStyle
     * @return array|object
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
    public function getLastInsertId(): string
    {
        return $this->driver->instance()->lastInsertId();
    }

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * 获取最后的日志
     * @return array
     */
    public function getQueryLog(): array
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
        return $this->raw($sql);
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
        return $this->raw($sql, ...$values);
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
            $tx->rollBack();
            throw $ex;
        }
    }

    /**
     * @return Transaction
     * @throws \PDOException
     */
    public function beginTransaction(): Transaction
    {
        return new Transaction($this->driver, $this->logger);
    }

    /**
     * 启动查询生成器
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table): QueryBuilder
    {
        return (new QueryBuilder($this))->table($table);
    }

}
