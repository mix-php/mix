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
     * 调度事件
     * @param $command
     * @param $arguments
     * @param $time
     */
    protected function dispatch($command, $arguments, $time)
    {
        if (!$this->dispatcher) {
            return;
        }
        $event            = new CalledEvent();
        $event->command   = $command;
        $event->arguments = $arguments;
        $event->time      = $time;
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
     * @param $command
     * @param array $arguments
     * @return mixed
     */
    public function __call($command, $arguments = [])
    {
        // 执行命令
        $microtime = static::microtime();
        $result    = call_user_func_array([$this->driver->instance(), $command], $arguments);
        $time      = round((static::microtime() - $microtime) * 1000, 2);
        // 调度执行事件
        $this->dispatch($command, $arguments, $time);
        // 返回
        return $result;
    }

}
