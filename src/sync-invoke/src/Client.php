<?php

namespace Mix\SyncInvoke;

use Mix\Bean\BeanInjector;
use Mix\Pool\ConnectionPoolInterface;
use Mix\SyncInvoke\Exception\CallException;
use Mix\SyncInvoke\Exception\InvokeException;

/**
 * Class Client
 * @package Mix\SyncInvoke
 * @deprecated 废弃该类，请使用
 */
class Client
{

    /**
     * 拨号器
     * @var Dialer
     */
    public $dialer;

    /**
     * 连接池
     * @var ConnectionPoolInterface
     */
    public $pool;

    /**
     * 连接
     * 为了兼容老版本直接传入连接，保留改参数为 public
     * @var Connection
     */
    public $connection;

    /**
     * Authorization constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * 获取连接
     * @return Connection
     */
    protected function getConnection()
    {
        if (isset($this->pool)) {
            return $this->pool->getConnection();
        }
        if (!isset($this->connection)) {
            $this->connection = $this->dialer->dial();
        }
        return $this->connection;
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
        $code       = \Opis\Closure\serialize($closure);
        $connection = $this->getConnection();
        $connection->send($code . Connection::EOF);
        $data = unserialize($connection->recv());
        $connection->release();
        if ($data instanceof CallException) {
            throw new InvokeException($data->message, $data->code);
        }
        return $data;
    }

}
