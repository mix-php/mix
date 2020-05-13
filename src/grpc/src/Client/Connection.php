<?php

namespace Mix\Grpc\Client;

use Mix\Bean\BeanInjector;
use Mix\Grpc\Client\Middleware\MiddlewareDispatcher;
use Mix\Grpc\Exception\InvokeException;

/**
 * Class Connection
 * @package Mix\Grpc\Client
 */
class Connection
{

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * @var float
     */
    public $timeout;

    /**
     * @var array MiddlewareInterface class or object
     */
    public $middleware = [];

    /**
     * @var \Swoole\Coroutine\Http2\Client
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
     * @throws InvokeException
     */
    public function connect()
    {
        $client = new \Swoole\Coroutine\Http2\Client($this->host, $this->port, false);
        $client->set([
            'timeout' => $this->timeout,
        ]);
        if (!$client->connect()) {
            throw new InvokeException($client->errMsg, $client->errCode);
        }
        $this->client = $client;
    }

    /**
     * Request
     * @param string $method
     * @param string $path
     * @param array $headers
     * @param string $body
     * @param float $timeout
     * @return \Swoole\Http2\Response
     * @throws InvokeException
     */
    public function request(string $method, string $path, array $headers = [], string $body = '', float $timeout = 5.0): \Swoole\Http2\Response
    {
        $request          = new \Swoole\Http2\Request();
        $request->method  = $method;
        $request->path    = $path;
        $request->headers = $headers + [
                'host'       => sprintf('%s:%s', $this->host, $this->port),
                'user-agent' => sprintf('Mix gRPC/PHP %s/Swoole %s', PHP_VERSION, SWOOLE_VERSION),
            ];
        $request->data    = $body;

        $process = function (\Swoole\Http2\Request $request) use ($timeout) {
            $this->send($request);
            return $this->recv($timeout);
        };

        $interceptDispatcher = new MiddlewareDispatcher($this->middleware, $process, $request);
        return $interceptDispatcher->dispatch();
    }

    /**
     * Send
     * @param \Swoole\Http2\Request $request
     * @throws InvokeException
     */
    protected function send(\Swoole\Http2\Request $request)
    {
        $client = $this->client;
        $result = $client->send($request);
        if ($result === false) {
            throw new InvokeException($client->errMsg, $client->errCode);
        }
    }

    /**
     * Recv
     * @param float $timeout
     * @return \Swoole\Http2\Response
     * @throws InvokeException
     */
    protected function recv(float $timeout)
    {
        $client = $this->client;
        $result = $client->recv($timeout);
        if ($result === false) {
            throw new InvokeException($client->errMsg, $client->errCode);
        }
        return $result;
    }

}
