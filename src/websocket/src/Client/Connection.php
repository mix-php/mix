<?php

namespace Mix\WebSocket\Client;

use Mix\Bean\BeanInjector;
use Mix\WebSocket\Exception\CloseFrameException;
use Mix\WebSocket\Exception\ReceiveException;
use Mix\WebSocket\Exception\UpgradeException;
use Swoole\Coroutine\Http\Client;

/**
 * Class Connection
 * @package Mix\WebSocket\Client
 */
class Connection
{

    /**
     * @var string
     */
    public $url = '';

    /**
     * @var float
     */
    public $timeout = 0.0;

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
     * @throws UpgradeException
     */
    public function connect()
    {
        $this->url = $url;
        $info      = parse_url($url);
        $host      = $info['host'] ?? '';
        $port      = $info['port'] ?? null;
        $ssl       = isset($info['scheme']) && $info['scheme'] == 'wss' ? true : false;
        $path      = ($info['path'] ?? '') . ($info['query'] ?? '') . ($info['fragment'] ?? '');
        $client    = $this->client = new Client($host, $port, $ssl);
        $client->set(['timeout' => $this->timeout]);
        if (!$client->upgrade($path)) {
            throw new UpgradeException(sprintf('WebSocket connect failed (%s)', $url));
        }
    }

    /**
     * Recv
     * @return \Swoole\WebSocket\Frame
     * @throws ReceiveException
     * @throws CloseFrameException
     */
    public function recv()
    {
        $frame = $this->client->recv(-1);
        if ($frame === false) { // 接收失败
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = swoole_last_error();
            $errMsg  = swoole_strerror($errCode, 9);
            throw new ReceiveException($errMsg, $errCode);
        }
        if ($frame instanceof \Swoole\WebSocket\CloseFrame) { // CloseFrame
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = $frame->code;
            $errMsg  = $frame->reason;
            throw new CloseFrameException($errMsg, $errCode);
        }
        if ($frame === "") { // 连接关闭
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = stripos(PHP_OS, 'Darwin') !== false ? 54 : 104; // mac=54, linux=104
            $errMsg  = swoole_strerror($errCode, 9);
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
            $socket = $this->swooleResponse->socket;
            throw new \Swoole\Exception($socket->errMsg ?: 'Send frame failed', $socket->errCode);
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
        $client  = $this->client;
        $errMsg  = $client->errMsg;
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
