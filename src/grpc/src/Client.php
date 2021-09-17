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
    protected $timeout = 5.0;

    /**
     * @var \Swoole\Coroutine\Http2\Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $channels = [];

    /**
     * @var bool
     */
    protected $reconnect = false;

    /**
     * @var bool
     */
    protected $closed = false;

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
        go(function () {
            while (true) {
                $response = $this->client->recv(-1);
                if ($this->closed) {
                    return;
                }
                if ($response === false) {
                    // 断线重连
                    try {
                        $this->reconnect();
                    } catch (\Throwable $ex) {
                        usleep(100000); // 0.1s
                    }
                    continue;
                }
                if (isset($this->channels[$response->streamId])) {
                    $this->channels[$response->streamId]->push($response);
                }
            }
        });
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

    public function close(): void
    {
        $this->closed = true;
        $this->client and $this->client->close();
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
        if ($this->closed) {
            throw new RuntimeException('The client has been closed');
        }
        $request = new \Swoole\Http2\Request();
        $request->method = $method;
        $request->path = $path;
        $request->headers = $headers + [
                'host' => sprintf('%s:%s', $this->host, $this->port),
                'user-agent' => sprintf('Mix gRPC/PHP %s/Swoole %s', PHP_VERSION, SWOOLE_VERSION),
            ];
        $request->data = $body;
        $streamId = $this->send($request);
        $channel = new \Swoole\Coroutine\Channel(1);
        $this->channels[$streamId] = $channel;
        $response = $channel->pop($this->timeout);
        $this->channels[$streamId] = null;
        unset($this->channels[$streamId]);
        if (!$response) {
            throw new RuntimeException(sprintf('Client stream %d request timeout', $streamId));
        }
        return $response;
    }

    /**
     * Send
     * @param \Swoole\Http2\Request $request
     * @return int
     * @throws RuntimeException
     */
    protected function send(\Swoole\Http2\Request $request): int
    {
        $client = $this->client;
        $streamId = $client->send($request);
        if ($streamId === false) {
            // 断线重连, 需要下次请求才能恢复正常
            $this->reconnect();
            throw new RuntimeException($client->errMsg, $client->errCode);
        }
        return $streamId;
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
