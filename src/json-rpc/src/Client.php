<?php

namespace Mix\JsonRpc;

use Mix\Bean\BeanInjector;
use Mix\JsonRpc\Call\Caller;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\JsonRpc\Pool\ConnectionPool;
use Mix\ServiceCenter\ServiceCenterInterface;

/**
 * Class Client
 * @package Mix\Sync\Invoke
 */
class Client
{

    /**
     * @var Dialer
     */
    public $dialer;

    /**
     * @var ServiceCenterInterface
     */
    public $serviceCenter;

    /**
     * 连接
     * @var Connection
     */
    public $connection;

    /**
     * Client constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
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
        if (!isset($this->connection)) {
            $this->connection = $this->dialer->dial();
        }
        return $this->connection;
    }

    /**
     * 通过服务调用
     * @param string $name
     * @return Caller
     */
    public function service(string $name)
    {
        $dialer     = $this->dialer;
        $service    = $this->serviceCenter->get($name);
        $connection = $dialer->dialService($service);
        return new Caller($connection);
    }

    /**
     * Call
     * @param Request $request
     * @return Response
     * @throws Exception\ParseException
     * @throws \Swoole\Exception
     */
    public function call(Request $request)
    {
        return (new Caller($this->getConnection()))->call($request);
    }

    /**
     * Multi Call
     * @param Request ...$requests
     * @return Response[]
     * @throws Exception\ParseException
     * @throws \Swoole\Exception
     */
    public function callMultiple(Request ...$requests)
    {
        return (new Caller($this->getConnection()))->callMultiple(...$requests);
    }

}
