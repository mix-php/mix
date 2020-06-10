<?php

namespace Mix\SyncInvoke\Client;

use Mix\SyncInvoke\Constants;
use Mix\SyncInvoke\Event\InvokedEvent;
use Mix\SyncInvoke\Exception\CallException;
use Mix\SyncInvoke\Exception\InvokeException;

/**
 * Class Connection
 * @package Mix\SyncInvoke\Client
 */
class Connection
{

    /**
     * @var Driver
     */
    public $driver;

    /**
     * Invoke timeout
     * @var float
     */
    public $invokeTimeout = 10.0;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * Connection constructor.
     * @param Driver $driver
     * @param float $invokeTimeout
     */
    public function __construct(Driver $driver, float $invokeTimeout)
    {
        $this->driver        = $driver;
        $this->invokeTimeout = $invokeTimeout;
    }

    /**
     * Connect
     * @throws \Swoole\Exception
     */
    public function connect()
    {
        $this->driver->connect();
    }

    /**
     * 关闭连接
     * @throws \Swoole\Exception
     */
    public function close()
    {
        $this->driver->close();
    }

    /**
     * Invoke
     * @param \Closure $closure
     * @return mixed
     * @throws InvokeException
     * @throws \Swoole\Exception
     */
    public function invoke(\Closure $closure)
    {
        $microtime = static::microtime();
        try {
            $code = \Opis\Closure\serialize($closure);
            $this->send($code . Constants::EOF);
            $data = unserialize($this->recv($this->invokeTimeout));
            if ($data instanceof CallException) {
                throw new InvokeException($data->message, $data->code);
            }
        } catch (\Throwable $ex) {
            $this->driver->__discard();
            $message = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
            $error   = sprintf('[%d] %s', $ex->getCode(), $message);
            throw $ex;
        } finally {
            $this->dispatch($code, $microtime, $error ?? null);
        }
        return $data;
    }

    /**
     * Recv
     * @param float $timeout
     * @return string
     * @throws \Swoole\Exception
     */
    protected function recv(float $timeout = -1)
    {
        $client = $this->driver->instance();
        $data   = $client->recv($timeout);
        if ($data === false) { // 接收失败
            throw new \Swoole\Exception($client->errMsg, $client->errCode);
        }
        if ($data === "") { // 连接关闭
            $errCode = stripos(PHP_OS, 'Darwin') !== false ? 54 : 104; // mac=54, linux=104
            $errMsg  = swoole_strerror($errCode, 9);
            throw new \Swoole\Exception($errMsg, $errCode);
        }
        return $data;
    }

    /**
     * Send
     * @param string $data
     * @throws \Swoole\Exception
     */
    protected function send(string $data)
    {
        $len    = strlen($data);
        $client = $this->driver->instance();
        $size   = $client->send($data);
        if ($size === false) {
            throw new \Swoole\Exception($client->errMsg, $client->errCode);
        }
        if ($len !== $size) {
            throw new \Swoole\Exception('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }
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
     * Dispatch
     * @param string $code
     * @param float $microtime
     * @param string|null $error
     */
    protected function dispatch(string $code, float $microtime, string $error = null)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $event        = new InvokedEvent();
        $event->time  = round((static::microtime() - $microtime) * 1000, 2);
        $event->code  = $code;
        $event->error = $error;
        $this->dispatcher->dispatch($event);
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        $this->driver->__return();
    }

}
