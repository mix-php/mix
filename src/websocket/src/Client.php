<?php

namespace Mix\WebSocket;

use Mix\WebSocket\Exception\CloseFrameException;
use Mix\WebSocket\Exception\ReceiveException;
use Mix\WebSocket\Exception\UpgradeException;

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
    }

    /**
     * Get default headers
     * @return array
     */
    protected function getDefaultHeaders()
    {
        return $defaultHeaders = [
            'User-Agent' => sprintf('Mix WebSocket/PHP %s/Swoole %s', PHP_VERSION, SWOOLE_VERSION),
        ];
    }

    /**
     * Connect
     * @throws UpgradeException
     */
    public function connect()
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
        $client->setHeaders($this->headers + $this->getDefaultHeaders());
        $client->setCookies($this->cookies);
        if (!$client->upgrade($path)) {
            throw new UpgradeException(sprintf('WebSocket connect failed (%s)', $url));
        }
        $this->client = $client;
    }

    /**
     * Recv
     * @param float $timeout
     * @return \Swoole\WebSocket\Frame
     * @throws ReceiveException
     * @throws CloseFrameException
     * @throws \Swoole\Exception
     */
    public function recv(float $timeout = -1)
    {
        $frame = $this->client->recv($timeout);
        if ($frame === false) { // 接收失败
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = swoole_last_error();
            if ($errCode == 0) {
                $errCode = stripos(PHP_OS, 'Darwin') !== false ? 54 : 104;
            }
            $errMsg = swoole_strerror($errCode, 9);
            throw new ReceiveException($errMsg, $errCode);
        }
        if ($frame instanceof \Swoole\WebSocket\CloseFrame) { // CloseFrame
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = $frame->code;
            $errMsg = $frame->reason;
            throw new CloseFrameException($errMsg, $errCode);
        }
        if ($frame === "") { // 连接关闭
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = stripos(PHP_OS, 'Darwin') !== false ? 54 : 104; // mac=54, linux=104
            $errMsg = swoole_strerror($errCode, 9);
            throw new ReceiveException($errMsg, $errCode);
        }
        return $frame;
    }

    /**
     * Send
     * @param \Swoole\WebSocket\Frame $data
     * @throws \Swoole\Exception
     */
    public function send(\Swoole\WebSocket\Frame $data)
    {
        $result = $this->client->push($data);
        if ($result === false) {
            throw new \Swoole\Exception(socket_strerror($this->client->errCode), $this->client->errCode);
        }
    }

    /**
     * Close
     * @throws \Swoole\Exception
     */
    public function close()
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
        if ($errMsg == 'Connection reset by peer' && in_array($errCode, [54, 104])) { // mac=54, linux=104
            return;
        }
        throw new \Swoole\Exception($errMsg, $errCode);
    }

}
