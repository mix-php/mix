<?php

namespace Mix\WebSocket;

use Mix\WebSocket\Exception\CloseFrameException;
use Mix\WebSocket\Exception\ReadMessageException;
use Mix\WebSocket\Exception\UpgradeException;
use Mix\WebSocket\Exception\WriteMessageException;
use Swoole\WebSocket\Frame;

/**
 * Class Client
 * @package Mix\WebSocket
 */
class Client
{

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string[]
     */
    protected $headers = [];

    /**
     * @var string[]
     */
    protected $cookies = [];

    /**
     * @var float
     */
    protected $timeout = 5.0;

    /**
     * @var \Swoole\Coroutine\Http\Client
     */
    protected $client;

    /**
     * Client constructor.
     * @param string $url
     * @param array $headers
     * @param array $cookies
     * @param float $timeout
     */
    public function __construct(string $url, array $headers = [], array $cookies = [], float $timeout = 5.0)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->cookies = $cookies;
        $this->timeout = $timeout;
        $this->connect();
    }

    /**
     * default headers
     * @return array
     */
    protected static function defaultHeaders(): array
    {
        return $defaultHeaders = [
            'User-Agent' => sprintf('Mix WebSocket/PHP %s/Swoole %s', PHP_VERSION, SWOOLE_VERSION),
        ];
    }

    /**
     * Connect
     * @throws UpgradeException
     */
    protected function connect(): void
    {
        $info = parse_url($this->url);
        $host = $info['host'] ?? '';
        $port = $info['port'] ?? null;
        $ssl = isset($info['scheme']) && $info['scheme'] == 'wss' ? true : false;
        if ($ssl && is_null($port)) {
            $port = 443;
        }
        $path = ($info['path'] ?? '') . ($info['query'] ?? '') . ($info['fragment'] ?? '');
        $client = $this->client = new \Swoole\Coroutine\Http\Client($host, $port, $ssl);
        $client->set(['timeout' => $this->timeout]);
        $client->setHeaders($this->headers + static::defaultHeaders());
        $client->setCookies($this->cookies);
        if (!$client->upgrade($path)) {
            throw new UpgradeException(sprintf('WebSocket upgrade failed (%s)', $this->url));
        }
        $this->client = $client;
    }

    /**
     * @param float $timeout
     * @return Frame|null
     * @throws ReadMessageException
     * @throws CloseFrameException
     */
    public function readMessage(float $timeout = -1): ?Frame
    {
        $frame = $this->client->recv($timeout);
        if (!$frame) { // 接收失败
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = $this->client->errCode;
            if ($errCode != 0) {
                $errMsg = socket_strerror($errCode);
                throw new WriteMessageException($errMsg, $errCode);
            }
            return null;
        }
        if ($frame instanceof \Swoole\WebSocket\CloseFrame) { // CloseFrame
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = $frame->code;
            $errMsg = $frame->reason;
            throw new CloseFrameException($errMsg, $errCode);
        }
        return $frame;
    }

    /**
     * Send
     * @param Frame $data
     * @throws WriteMessageException
     */
    public function writeMessage(Frame $data): void
    {
        $result = $this->client->push($data);
        if ($result === false) {
            $errCode = $this->client->errCode;
            if ($errCode != 0) {
                $errMsg = socket_strerror($errCode);
                throw new WriteMessageException($errMsg, $errCode);
            }
        }
    }

    /**
     * Close
     * @throws \Swoole\Exception
     */
    public function close(): void
    {
        if ($this->client->close()) {
            return;
        }

        $client = $this->client;
        $errMsg = $client->errMsg;
        $errCode = $client->errCode;
        if ($errMsg == '' && $errCode == 0) {
            return;
        }
        if ($errMsg == 'Connection reset by peer') {
            return;
        }
        throw new \Swoole\Exception($errMsg, $errCode);
    }

}
