<?php

namespace Mix\JsonRpc\Client;

use Mix\Bean\BeanInjector;
use Mix\JsonRpc\Call\Caller;
use Mix\JsonRpc\Constants;
use Mix\JsonRpc\Exception\ParseException;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\JsonRpc\Middleware\MiddlewareDispatcher;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Swoole\Coroutine\Client;

/**
 * Class Connection
 * @package Mix\JsonRpc\Client
 */
class Connection
{

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var int
     */
    public $port = 0;

    /**
     * Global timeout
     * @var float
     */
    public $timeout = 0.0;

    /**
     * Call timeout
     * @var float
     */
    public $callTimeout = 10.0;

    /**
     * @var MiddlewareInterface[]
     */
    public $middleware = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * Connection constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Connect
     * @throws \Swoole\Exception
     */
    public function connect()
    {
        $host    = $this->host;
        $port    = $this->port;
        $timeout = $this->timeout;
        $client  = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_eof_check' => true,
            'package_eof'    => Constants::EOF,
        ]);
        if (!$client->connect($host, $port, $timeout)) {
            throw new \Swoole\Exception(sprintf("JSON-RPC: %s (host:%s, port: %s)", $client->errMsg, $host, $port), $client->errCode);
        }
        $this->client = $client;
    }

    /**
     * Call
     * @param Request $request
     * @return Response
     * @throws Exception\ParseException
     * @throws ParseException
     * @throws \Swoole\Exception
     */
    public function call(Request $request)
    {
        $process             = function (array $requests) {
            $request    = array_pop($requests);
            $jsonString = JsonRpcHelper::encode($request) . Constants::EOF;
            $this->send($jsonString);
            $data = $this->recv($this->callTimeout);
            return JsonRpcHelper::parseResponses($data);
        };
        $interceptDispatcher = new MiddlewareDispatcher($this->middleware, $process, [$request]);
        $responses           = $interceptDispatcher->dispatch();
        return array_pop($responses);
    }

    /**
     * Multi Call
     * @param Request ...$requests
     * @return Response[]
     * @throws ParseException
     * @throws \Swoole\Exception
     */
    public function callMultiple(Request ...$requests)
    {
        if (empty($requests)) {
            return [];
        }
        $process             = function (array $requests) {
            if (count($requests) == 1) {
                $jsonStr = JsonRpcHelper::encode(array_pop($requests)) . Constants::EOF;
            } else {
                $jsonStr = JsonRpcHelper::encode($requests) . Constants::EOF;
            }
            $this->send($jsonStr);
            $data = $this->recv($this->callTimeout);
            return JsonRpcHelper::parseResponses($data);
        };
        $interceptDispatcher = new MiddlewareDispatcher($this->middleware, $process, $requests);
        return $interceptDispatcher->dispatch();
    }

    /**
     * 关闭连接
     * @throws \Swoole\Exception
     */
    public function close()
    {
        if (!$this->client->close()) {
            $errMsg  = $this->client->errMsg;
            $errCode = $this->client->errCode;
            if ($errMsg == '' && $errCode == 0) {
                return;
            }
            throw new \Swoole\Exception($errMsg, $errCode);
        }
    }

    /**
     * Recv
     * @param float $timeout
     * @return string
     * @throws \Swoole\Exception
     */
    protected function recv(float $timeout = -1)
    {
        $data = $this->client->recv(-1);
        if ($data === false || $data === "") {
            throw new \Swoole\Exception($this->client->errMsg, $this->client->errCode);
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
        $size = $this->client->send($data);
        if ($size === false) {
            throw new \Swoole\Exception($this->client->errMsg, $this->client->errCode);
        }
        if ($len !== $size) {
            throw new \Swoole\Exception('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }
    }

}
