<?php

namespace Mix\Grpc;

use Mix\Grpc\Exception\RuntimeException;

/**
 * Class Client
 * @package Mix\Grpc
 */
class Client
{

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var bool
     */
    protected $ssl;

    /**
     * @var float
     */
    protected $timeout;

    /**
     * @var \Swoole\Coroutine\Http2\Client
     */
    protected $client;

    /**
     * @var bool
     */
    protected $reconnect = false;

    /**
     * Client constructor.
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @param float $timeout
     */
    public function __construct(string $host, int $port, bool $ssl = false, float $timeout = 5.0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->ssl = $ssl;
        $this->timeout = $timeout;
        $this->connect();
    }

    /**
     * @throws RuntimeException
     */
    protected function connect(): void
    {
        $client = new \Swoole\Coroutine\Http2\Client($this->host, $this->port, $this->ssl);
        $client->set([
            'timeout' => $this->timeout,
        ]);
        if (!$client->connect()) {
            throw new RuntimeException($client->errMsg, $client->errCode);
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
     * @throws RuntimeException
     */
    public function request(string $method, string $path, array $headers = [], string $body = '', float $timeout = 5.0): \Swoole\Http2\Response
    {
        $request = new \Swoole\Http2\Request();
        $request->method = $method;
        $request->path = $path;
        $request->headers = $headers + [
                'host' => sprintf('%s:%s', $this->host, $this->port),
                'user-agent' => sprintf('Mix gRPC/PHP %s/Swoole %s', PHP_VERSION, SWOOLE_VERSION),
            ];
        $request->data = $body;
        $this->send($request);
        return $this->recv($timeout);
    }

    /**
     * Send
     * @param \Swoole\Http2\Request $request
     * @throws RuntimeException
     */
    protected function send(\Swoole\Http2\Request $request): void
    {
        $client = $this->client;
        $result = $client->send($request);
        if ($result === false) {
            // 断线重连, 需要下次请求才能恢复正常
            $this->reconnect();

            throw new RuntimeException($client->errMsg, $client->errCode);
        }
    }

    /**
     * Recv
     * @param float $timeout
     * @return \Swoole\Http2\Response
     * @throws RuntimeException
     */
    protected function recv(float $timeout): \Swoole\Http2\Response
    {
        $client = $this->client;
        $result = $client->recv($timeout);
        if ($result === false) {
            // 断线重连, 需要下次请求才能恢复正常
            $this->reconnect();

            throw new RuntimeException($client->errMsg, $client->errCode);
        }
        return $result;
    }

    /**
     * @throws RuntimeException
     */
    protected function reconnect(): void
    {
        if (!$this->reconnect && strpos($this->client->errMsg, 'Broken pipe') !== false) {
            try {
                $this->reconnect = true;
                $this->connect();
            } finally {
                $this->reconnect = false;
            }
        }
    }

}
