<?php

namespace Mix\Redis\Subscribe;

/**
 * Class Connection
 * @package Mix\Redis\Subscribe
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
    public $port = 6379;

    /**
     * @var float
     */
    public $timeout = 0.0;

    /**
     * @var \Swoole\Coroutine\Client
     */
    protected $client;

    /**
     * @var bool
     */
    protected $closed = false;

    /**
     * EOF
     */
    const EOF = "\r\n";

    /**
     * Connection constructor.
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @throws \Swoole\Exception
     */
    public function __construct(string $host, int $port, float $timeout = 5.0)
    {
        $this->host    = $host;
        $this->port    = $port;
        $this->timeout = $timeout;
        $client        = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_eof_check' => true,
            'package_eof'    => static::EOF,
        ]);
        if (!$client->connect($host, $port, $timeout)) {
            throw new \Swoole\Exception(sprintf('Redis connect failed (host: %s, port: %s)', $host, $port));
        }
        $this->client = $client;
    }

    /**
     * Send
     * @param string $data
     * @return bool
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
        return true;
    }

    /**
     * Recv
     * @return string|bool
     */
    public function recv()
    {
        return $this->client->recv(-1);
    }

    /**
     * Close
     */
    public function close()
    {
        if (!$this->closed && !$this->client->close()) {
            $errMsg  = $this->client->errMsg;
            $errCode = $this->client->errCode;
            if ($errMsg == '' && $errCode == 0) {
                return;
            }
            throw new \Swoole\Exception($errMsg, $errCode);
        }
        $this->closed = true;
    }

}
