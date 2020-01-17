<?php

namespace Mix\JsonRpc;

use Mix\Bean\BeanInjector;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\Pool\ConnectionPoolInterface;

/**
 * Class Client
 * @package Mix\Sync\Invoke
 */
class Client
{

    /**
     * 连接池
     * @var ConnectionPoolInterface
     */
    public $pool;

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
        return $this->pool ? $this->pool->getConnection() : $this->connection;
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
        $jsonStr    = JsonRpcHelper::encode($request) . Constants::EOF;
        $connection = $this->getConnection();
        $connection->send($jsonStr);
        $data = $connection->recv();
        $connection->release();
        $responses = JsonRpcHelper::parseResponses($data);
        return array_pop($responses);
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
        if (empty($requests)) {
            return [];
        }
        if (count($requests) == 1) {
            $jsonStr = JsonRpcHelper::encode(array_pop($requests)) . Constants::EOF;
        } else {
            $jsonStr = JsonRpcHelper::encode($requests) . Constants::EOF;
        }
        $connection = $this->getConnection();
        $connection->send($jsonStr);
        $data = $connection->recv();
        $connection->release();
        return JsonRpcHelper::parseResponses($data);
    }

}
