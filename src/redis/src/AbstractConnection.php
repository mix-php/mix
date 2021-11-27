<?php

namespace Mix\Redis;

/**
 * Class AbstractConnection
 * @package Mix\Redis
 */
abstract class AbstractConnection implements ConnectionInterface
{

    use ScanTrait;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * AbstractConnection constructor.
     * @param Driver $driver
     * @param LoggerInterface|null $logger
     */
    public function __construct(Driver $driver, ?LoggerInterface $logger)
    {
        $this->driver = $driver;
        $this->logger = $logger;
    }

    /**
     * 连接
     * @throws \RedisException
     */
    public function connect()
    {
        $this->driver->connect();
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->driver->close();
    }

    /**
     * 重新连接
     * @throws \RedisException
     */
    protected function reconnect()
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
            'went away',
            'failed with errno',
            'connection lost',
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
     * @return array
     */
    public function exec()
    {
        throw new \RedisException('Start the transaction using the multi pipeline method');
    }

    public function unwatch()
    {
        throw new \RedisException('Not implemented');
    }

    /**
     * @param string ...$keys
     * @return Multi
     */
    public function watch(string ...$keys): Multi
    {
        $this->__call('watch', [$keys]);

        $driver = $this->driver;
        $this->driver = null; // 使其在析构时不回收
        return new Multi($driver, $this->logger, true);
    }

    /**
     * @return Multi
     */
    public function multi(): Multi
    {
        $driver = $this->driver;
        $this->driver = null; // 使其在析构时不回收
        return new Multi($driver, $this->logger);
    }

    /**
     * @return Pipeline
     */
    public function pipeline(): Pipeline
    {
        $driver = $this->driver;
        $this->driver = null; // 使其在析构时不回收
        return new Pipeline($driver, $this->logger);
    }

    /**
     * 执行命令
     * @param string $command
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
    public function __call(string $command, array $arguments = [])
    {
        $beginTime = microtime(true);

        try {
            $result = call_user_func_array([$this->driver->instance(), $command], $arguments);
        } catch (\Throwable $ex) {
            throw $ex;
        } finally {
            // 记录执行时间
            $time = round((microtime(true) - $beginTime) * 1000, 2);

            // logger
            if ($this->logger) {
                $this->logger->trace(
                    $time,
                    $command,
                    $arguments,
                    $ex ?? null
                );
            }
        }

        return $result;
    }

}
