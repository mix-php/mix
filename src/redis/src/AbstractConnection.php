<?php

namespace Mix\Redis;

use Mix\Bean\BeanInjector;
use Mix\Redis\Event\CalledEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractConnection
 * @package Mix\Redis
 * @author liu,jian <coder.keda@gmail.com>
 */
abstract class AbstractConnection
{

    /**
     * 主机
     * @var string
     */
    public $host = '';

    /**
     * 端口
     * @var int
     */
    public $port = 6379;

    /**
     * 超时
     * @var float
     */
    public $timeout = 5.0;

    /**
     * 重连间隔
     * @var int
     */
    public $retryInterval = 0;

    /**
     * 读取超时
     * phpredis >= 3.1.3
     * @var int
     */
    public $readTimeout = -1;

    /**
     * 数据库
     * @var int
     */
    public $database = 0;

    /**
     * 密码
     * @var string
     */
    public $password = '';

    /**
     * 事件调度器
     * @deprecated 废弃，改用 dispatcher
     * @var EventDispatcherInterface
     */
    public $eventDispatcher;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * redis对象
     * @var \Redis
     */
    protected $_redis;

    /**
     * Connection constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 创建连接
     * @return \Redis
     * @throws \RedisException
     */
    protected function createConnection()
    {
        $redis  = new \Redis();
        $result = $redis->connect($this->host, $this->port, $this->timeout, null, $this->retryInterval);
        if ($result === false) {
            throw new \RedisException(sprintf('Redis connect failed (host: %s, port: %s)', $this->host, $this->port));
        }
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, $this->readTimeout);
        // 假设密码是字符串 0 也能通过这个校验
        if ('' != (string)$this->password) {
            $redis->auth($this->password);
        }
        $redis->select($this->database);
        return $redis;
    }

    /**
     * 连接
     * @return bool
     */
    public function connect()
    {
        $this->_redis = $this->createConnection();
        return true;
    }

    /**
     * 关闭连接
     * @return bool
     */
    public function close()
    {
        if (!isset($this->_redis)) {
            return true;
        }
        $this->_redis->close();
        $this->_redis = null;
        return true;
    }

    /**
     * 调度事件
     * @param $command
     * @param $arguments
     * @param $time
     */
    protected function dispatchEvent($command, $arguments, $time)
    {
        if (!$this->dispatcher && !$this->eventDispatcher) {
            return;
        }
        $event            = new CalledEvent();
        $event->command   = $command;
        $event->arguments = $arguments;
        $event->time      = $time;
        $this->dispatcher and $this->dispatcher->dispatch($event);
        $this->eventDispatcher and $this->eventDispatcher->dispatch($event);
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
     * 执行命令
     * @param $command
     * @param array $arguments
     * @return mixed
     */
    public function __call($command, $arguments = [])
    {
        // 执行命令
        $microtime = static::microtime();
        $result    = call_user_func_array([$this->_redis, $command], $arguments);
        $time      = round((static::microtime() - $microtime) * 1000, 2);
        // 调度执行事件
        $this->dispatchEvent($command, $arguments, $time);
        // 返回
        return $result;
    }

    /**
     * 遍历key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return array|bool
     */
    public function scan(&$iterator, $pattern = '', $count = 0)
    {
        // $iterator 必须要加 &
        return $this->__call(__FUNCTION__, [&$iterator, $pattern, $count]);
    }

    /**
     * 遍历set key
     * @param $key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return array|bool
     */
    public function sScan($key, &$iterator, $pattern = '', $count = 0)
    {
        // $iterator 必须要加 &
        return $this->__call(__FUNCTION__, [$key, &$iterator, $pattern, $count]);
    }

    /**
     * 遍历zset key
     * @param $key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return array|bool
     */
    public function zScan($key, &$iterator, $pattern = '', $count = 0)
    {
        // $iterator 必须要加 &
        return $this->__call(__FUNCTION__, [$key, &$iterator, $pattern, $count]);
    }

    /**
     * 遍历hash key
     * @param $key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return array
     */
    public function hScan($key, &$iterator, $pattern = '', $count = 0)
    {
        // $iterator 必须要加 &
        return $this->__call(__FUNCTION__, [$key, &$iterator, $pattern, $count]);
    }

}
