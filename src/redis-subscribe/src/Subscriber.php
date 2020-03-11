<?php

namespace Mix\Redis\Subscribe;

use Mix\Redis\Subscribe\Exception\SubscribeException;
use Mix\Redis\Subscribe\Exception\UnsubscribeException;

/**
 * Class Subscriber
 * @package Mix\Redis\Subscribe
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
     * 超时
     * @var float
     */
    public $timeout = 0.0;

    /**
     * 密码
     * @var string
     */
    public $password = '';

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
     * @throws \Throwable
     */
    public function __construct(string $host, int $port, string $password = '', float $timeout = 5.0)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->password = $password;
        $this->timeout  = $timeout;
        $this->connect();
    }

    /**
     * Connect
     * @throws \Swoole\Exception
     * @throws \Throwable
     */
    protected function connect()
    {
        $connection           = new Connection($this->host, $this->port, $this->timeout);
        $this->commandInvoker = new CommandInvoker($connection);
        if ('' != (string)$this->password) {
            $this->commandInvoker->invoke("auth {$this->password}", 1);
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
        $result = $this->commandInvoker->invoke("subscribe " . join(' ', $channels), count($channels));
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
        $result = $this->commandInvoker->invoke("unsubscribe " . join(' ', $channels), count($channels));
        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new UnsubscribeException('Unsubscribe failed');
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

}
