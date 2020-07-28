<?php

namespace Mix\Redis;

use Mix\Redis\Event\CalledEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractConnection
 * @package Mix\Redis
 * @author liu,jian <coder.keda@gmail.com>
 */
abstract class AbstractConnection implements ConnectionInterface
{

    /**
     * 驱动
     * @var Driver
     */
    public $driver;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * AbstractConnection constructor.
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
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
     * @param \Throwable $e
     * @return bool
     */
    protected static function isDisconnectException(\Throwable $ex)
    {
        $disconnectMessages = [
            'failed with errno',
            'connection lost',
        ];
        $errorMessage       = $ex->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 调度事件
     * @param string $command
     * @param array $arguments
     * @param float $time
     * @param string|null $error
     */
    protected function dispatch(string $command, array $arguments, float $time, string $error = null)
    {
        if (!$this->dispatcher) {
            return;
        }
        $event            = new CalledEvent();
        $event->command   = $command;
        $event->arguments = $arguments;
        $event->time      = $time;
        $event->error     = $error;
        $this->dispatcher->dispatch($event);
    }

    /**
     * 获取当前时间, 单位: 秒, 粒度: 微秒
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * 执行命令
     * @param string $command
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $command, array $arguments = [])
    {
        $microtime = static::microtime();
        try {
            $result = call_user_func_array([$this->driver->instance(), $command], $arguments);
        } catch (\Throwable $ex) {
            $message = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
            $code    = $ex->getCode();
            $error   = sprintf('[%d] %s', $code, $message);
            throw $ex;
        } finally {
            $time = round((static::microtime() - $microtime) * 1000, 2);
            $this->dispatch($command, $arguments, $time, $error ?? null);
        }
        return $result;
    }

}
