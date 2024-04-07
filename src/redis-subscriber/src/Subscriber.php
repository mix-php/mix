<?php

namespace Mix\Redis\Subscriber;

use Mix\Redis\Subscriber\Exception\SubscribeException;
use Mix\Redis\Subscriber\Exception\UnsubscribeException;

/**
 * Class Subscriber
 * @package Mix\Redis\Subscriber
 */
class Subscriber
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
     * 密码
     * @var string
     */
    public $password = '';

    /**
     * 超时
     * @var float
     */
    public $timeout = 5.0;

    /**
     * 前缀
     * @var string
     */
    public $prefix = '';

    /**
     * 命令调用器
     * @var CommandInvoker
     */
    protected $commandInvoker;

    /**
     * 是否已关闭
     * @var bool
     */
    public $closed = false;

    /**
     * Subscriber constructor.
     * @param string $host
     * @param int $port
     * @param string $password
     * @param float $timeout
     * @throws \Swoole\Exception
     */
    public function __construct(string $host, int $port = 6379, string $password = '', float $timeout = 5.0, string $prefix = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
        $this->prefix = $prefix;
        $this->connect();
    }

    /**
     * Connect
     * @throws \Swoole\Exception
     */
    protected function connect()
    {
        $connection = new Connection($this->host, $this->port, $this->timeout);
        $this->commandInvoker = new CommandInvoker($connection);
        if ('' != (string)$this->password) {
            $this->commandInvoker->invoke(["auth", $this->password], 1);
        }
    }

    /**
     * Subscribe
     * @param string ...$channels
     * @throws \Swoole\Exception
     * @throws \Throwable
     */
    public function subscribe(string ...$channels)
    {
        $channels = array_map(function ($channel) {
            return $this->prefix . $channel;
        }, $channels);
        $result = $this->commandInvoker->invoke(["subscribe", ...$channels], count($channels));
        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new SubscribeException('Subscribe failed');
            }
        }
    }

    /**
     * Unsubscribe
     * @param string ...$channels
     * @throws \Swoole\Exception
     * @throws \Throwable
     */
    public function unsubscribe(string ...$channels)
    {
        $channels = array_map(function ($channel) {
            return $this->prefix . $channel;
        }, $channels);
        $result = $this->commandInvoker->invoke(["unsubscribe", ...$channels], count($channels));
        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new UnsubscribeException('Unsubscribe failed');
            }
        }
    }

    /**
     * PSubscribe.
     * @throws \Swoole\Exception
     * @throws Throwable
     */
    public function psubscribe(string ...$channels)
    {
        $channels = array_map(function ($channel) {
            return $this->prefix . $channel;
        }, $channels);
        $result = $this->commandInvoker->invoke(['psubscribe', ...$channels], count($channels));
        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new SubscribeException('Psubscribe failed');
            }
        }
    }

    /**
     * PUnsubscribe.
     * @throws \Swoole\Exception
     * @throws Throwable
     */
    public function punsubscribe(string ...$channels)
    {
        $channels = array_map(function ($channel) {
            return $this->prefix . $channel;
        }, $channels);
        $result = $this->commandInvoker->invoke(['punsubscribe', ...$channels], count($channels));
        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new UnsubscribeException('Punsubscribe failed');
            }
        }
    }

    /**
     * Channel
     * @return \Swoole\Coroutine\Channel
     */
    public function channel()
    {
        return $this->commandInvoker->channel();
    }

    /**
     * Close
     * @throws \Swoole\Exception
     */
    public function close()
    {
        $this->closed = true;
        $this->commandInvoker->interrupt();
    }

    /**
     * Ping
     * @param int $timeout
     * @throws \Swoole\Exception
     */
    public function ping(int $timeout = 1)
    {
        return $this->commandInvoker->ping($timeout);
    }

}
