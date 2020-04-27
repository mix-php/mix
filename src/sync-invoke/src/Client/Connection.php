<?php

namespace Mix\SyncInvoke\Client;

use Mix\SyncInvoke\Constants;
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
     * Connection constructor.
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
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
        try {
            $code = \Opis\Closure\serialize($closure);
            $this->send($code . Constants::EOF);
            $data = unserialize($this->recv($this->invokeTimeout));
            if ($data instanceof CallException) {
                throw new InvokeException($data->message, $data->code);
            }
        } catch (\Throwable $ex) {
            $this->driver->__discard();
            throw $ex;
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
        $data = $this->driver->instance()->recv($timeout);
        if ($data === false) { // 接收失败
            $client = $this->client;
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
        $len  = strlen($data);
        $size = $this->driver->instance()->send($data);
        if ($size === false) {
            throw new \Swoole\Exception($this->client->errMsg, $this->client->errCode);
        }
        if ($len !== $size) {
            throw new \Swoole\Exception('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        $this->driver->__return();
    }

}
