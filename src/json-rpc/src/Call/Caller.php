<?php

namespace Mix\JsonRpc\Call;

use Mix\JsonRpc\Connection;
use Mix\JsonRpc\Constants;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Class Caller
 * @package Mix\JsonRpc\Call
 */
class Caller
{
    
    /**
     * 连接
     * @var Connection
     */
    public $connection;

    /**
     * Caller constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
        $connection = $this->connection;
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
        $connection = $this->connection;
        $connection->send($jsonStr);
        $data = $connection->recv();
        $connection->release();
        return JsonRpcHelper::parseResponses($data);
    }

}
