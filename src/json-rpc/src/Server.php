<?php

namespace Mix\JsonRpc;

use Mix\Concurrent\Sync\WaitGroup;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\HandlerInterface;
use Mix\JsonRpc\Factory\ResponseFactory;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\Server\Connection;
use Mix\Server\Exception\ReceiveException;
use Swoole\Coroutine\Channel;

/**
 * Class Server
 * @package Mix\JsonRpc
 */
class Server implements HandlerInterface
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
     * @var bool
     */
    public $reusePort = false;

    /**
     * @var \Mix\Server\Server
     */
    protected $server;

    /**
     * 服务集合
     * @var callable[]
     */
    protected $services = [];

    /**
     * Server constructor.
     * @param string $host
     * @param int $port
     * @param bool $reusePort
     */
    public function __construct(string $host, int $port, bool $reusePort = false)
    {
        $this->host      = $host;
        $this->port      = $port;
        $this->reusePort = $reusePort;
    }

    /**
     * Register
     * @param object $service
     */
    public function register(object $service)
    {
        $name    = str_replace('/', '\\', basename(str_replace('\\', '/', get_class($service))));
        $methods = get_class_methods($service);
        foreach ($methods as $method) {
            $this->services[sprintf('%s.%s', $name, $method)] = [$service, $method];
        }
    }

    /**
     * Start
     * @throws \Swoole\Exception
     */
    public function start()
    {
        $server = $this->server = new \Mix\Server\Server($this->host, $this->port, false, $this->reusePort);
        $server->set([
            'open_eof_check' => true,
            'package_eof'    => Constants::EOF,
        ]);
        $server->handle(function (Connection $conn) {
            $this->handleTCP($conn);
        });
        $server->start();
    }

    /**
     * 连接处理
     * @param Connection $conn
     * @throws \Throwable
     */
    protected function handleTCP(Connection $conn)
    {
        // 消息发送
        $sendChan = new Channel();
        xdefer(function () use ($sendChan) {
            $sendChan->close();
        });
        xgo(function () use ($sendChan, $conn) {
            while (true) {
                $data = $sendChan->pop();
                if (!$data) {
                    return;
                }
                try {
                    $conn->send($data);
                } catch (\Throwable $e) {
                    $conn->close();
                    throw $e;
                }
            }
        });
        // 消息读取
        while (true) {
            try {
                $data = $conn->recv();
                $this->callTCP($sendChan, $data);
            } catch (\Throwable $e) {
                // 忽略服务器主动断开连接异常
                if ($e instanceof ReceiveException) {
                    return;
                }
                // 抛出异常
                throw $e;
            }
        }
    }

    /**
     * 调用TCP
     * @param Channel $sendChan
     * @param string $content
     */
    protected function callTCP(Channel $sendChan, string $content)
    {
        /**
         * 解析
         * @var Request[] $requests
         * @var bool $single
         */
        try {
            list($single, $requests) = JsonRpcHelper::parseRequests($content);
        } catch (\Throwable $ex) {
            $response = (new ResponseFactory)->createErrorResponse(-32700, 'Parse error', null);
            $sendChan->push(JsonRpcHelper::content(true, $response));
            return;
        }
        // 处理
        $responses = $this->process(...$requests);
        // 发送
        $sendChan->push(JsonRpcHelper::content($single, ...$responses));
    }

    /**
     * 调用HTTP
     * @param \Mix\Http\Message\Response $httpResponse
     * @param string $content
     */
    protected function callHTTP(\Mix\Http\Message\Response $httpResponse, string $content)
    {
        /**
         * 解析
         * @var Request[] $requests
         * @var bool $single
         */
        try {
            list($single, $requests) = JsonRpcHelper::parseRequests($content);
        } catch (\Throwable $ex) {
            $response = (new ResponseFactory)->createErrorResponse(-32700, 'Parse error', null);
            $body     = (new StreamFactory)->createStream(JsonRpcHelper::content(true, $response));
            $httpResponse->withBody($body)
                ->withContentType('application/json')
                ->withStatus(200)
                ->end();
            return;
        }
        // 处理
        $responses = $this->process(...$requests);
        // 发送
        $body = (new StreamFactory)->createStream(JsonRpcHelper::content($single, ...$responses));
        $httpResponse->withBody($body)
            ->withContentType('application/json')
            ->withStatus(200)
            ->end();
    }

    /**
     * 处理
     * @param Request ...$requests
     * @return array
     */
    protected function process(Request ...$requests)
    {
        $waitGroup = WaitGroup::new();
        $waitGroup->add(count($requests));
        $responses = [];
        foreach ($requests as $request) {
            xgo(function () use ($request, &$responses, $waitGroup) {
                xdefer(function () use ($waitGroup) {
                    $waitGroup->done();
                });
                // 验证
                if (!JsonRpcHelper::validRequest($request)) {
                    $responses[] = (new ResponseFactory)->createErrorResponse(-32600, 'Invalid Request', $request->id);
                    return;
                }
                if (!isset($this->services[$request->method])) {
                    $responses[] = (new ResponseFactory)->createErrorResponse(-32601, 'Method not found', $request->id);
                    return;
                }
                // 执行
                try {
                    $result      = call_user_func($this->services[$request->method], ...$request->params);
                    $result      = is_scalar($result) ? [$result] : $result;
                    $responses[] = (new ResponseFactory)->createResultResponse($result, $request->id);
                } catch (\Throwable $ex) {
                    $responses[] = (new ResponseFactory)->createErrorResponse($ex->getCode(), $ex->getMessage(), $request->id);
                }
            });
        }
        $waitGroup->wait();
        return $responses;
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param \Mix\Http\Message\Response $response
     */
    public function HandleHTTP(ServerRequest $request, \Mix\Http\Message\Response $response)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false) {
            $response->withStatus(500)->end();
            return;
        }
        $content = $request->getBody()->getContents();
        $this->callHTTP($response, $content);
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        $this->server->shutdown();
    }

}
