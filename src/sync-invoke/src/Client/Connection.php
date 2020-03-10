<?php

namespace Mix\SyncInvoke\Client;

use Mix\Bean\BeanInjector;
use Mix\Pool\ConnectionTrait;
use Mix\SyncInvoke\Exception\CallException;
use Mix\SyncInvoke\Exception\InvokeException;
use Swoole\Coroutine\Client;

/**
 * Class Connection
 * @package Mix\SyncInvoke\Client
 */
class Connection
{

    use ConnectionTrait;

    /**
     * @var int
     */
    public $port = 0;

    /**
     * @var float
     */
    public $timeout = 0.0;

    /**
     * @var Client
     */
    protected $client;

    /**
     * EOF
     */
    const EOF = "-Y3ac0v\n";

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
        $this->port    = $port;
        $this->timeout = $timeout;
        $client        = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_eof_check' => true,
            'package_eof'    => static::EOF,
        ]);
        if (!$client->connect('127.0.0.1', $port, $timeout)) {
            throw new \Swoole\Exception(sprintf("Sync invoke: %s (port: %s)", $client->errMsg, $port), $client->errCode);
        }
        $this->client = $client;
    }

    /**
     * 析构
     */
    public function __destruct()
    {
        // 丢弃连接
        $this->discard();
    }

    /**
     * Recv
     * @return string
     * @throws \Swoole\Exception
     */
    public function recv()
    {
        $data = $this->client->recv(-1);
        if ($data === false) { // 接收失败
            $client = $this->client;
            throw new \Swoole\Exception($client->errMsg, $client->errCode);
        }
        if ($data === "") { // 连接关闭
            $errCode = stripos(PHP_OS, 'Darwin') !== false ? 54 : 104; // mac=54, linux=104
            $errMsg  = swoole_strerror($errCode, 9);
            throw new \Swoole\Exception($errMsg, $errCode);
        }
        return $data;
    }

    /**
     * Send
     * @param string $data
     * @throws \Swoole\Exception
     */
    public function send(string $data)
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

    /**
     * Invoke
     * @param \Closure $closure
     * @return mixed
     * @throws InvokeException
     * @throws \Swoole\Exception
     */
    public function invoke(\Closure $closure)
    {
        $code = \Opis\Closure\serialize($closure);
        $this->send($code . Connection::EOF);
        $data = unserialize($this->recv());
        if ($data instanceof CallException) {
            throw new InvokeException($data->message, $data->code);
        }
        return $data;
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

}
