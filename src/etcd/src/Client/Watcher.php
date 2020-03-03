<?php

namespace Mix\Etcd\Client;

use Mix\Concurrent\Timer;

/**
 * Class Watcher
 * @package Mix\Etcd\Client
 */
class Watcher
{

    /**
     * @var string host:port
     */
    public $server;

    /**
     * @var string
     */
    public $prefix = '';

    /**
     * @var \Closure
     */
    public $func;

    /**
     * @var bool
     */
    protected $closed = false;

    /**
     * @var \Swoole\Coroutine\Client
     */
    protected $client;

    /**
     * Connect timeout
     * @var int
     */
    protected $timeout = 3;

    /**
     * Watcher constructor.
     * @param string $server
     * @param string $prefix
     * @param \Closure $func
     */
    public function __construct(string $server, string $prefix, \Closure $func)
    {
        $this->server = $server;
        $this->prefix = $prefix;
        $this->func   = $func;
    }

    /**
     * Create client
     * @return \Swoole\Coroutine\Client
     * @throws \Swoole\Exception
     */
    protected function createClient()
    {
        $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_eof_check' => true,
            'package_eof'    => "\n",
        ]);
        list($address, $port) = explode(':', $this->server);
        if (!$client->connect($address, (int)$port, $this->timeout)) {
            throw new \Swoole\Exception(sprintf("Etcd client connect failed, %s (%s)", $client->errMsg, $server), $client->errCode);
        }
        return $client;
    }

    /**
     * Print log
     * @param \Throwable $ex
     */
    protected function log(\Throwable $ex)
    {
        $time    = date('[error] Y-m-d H:i:s');
        $message = sprintf('%s [%d]', $ex->getMessage(), $ex->getCode());
        echo "$time $message";
    }

    /**
     * Watch forever
     */
    public function forever()
    {
        xgo(function () {
            while (true) {
                if ($this->closed) {
                    return;
                }
                try {
                    $this->watch();
                } catch (\Throwable $ex) {
                    $this->log($ex);
                }
            }
        });
    }

    /**
     * Watch
     * @throws \Swoole\Exception
     */
    public function watch()
    {
        $client               = $this->client = $this->createClient();
        $prefix               = $this->prefix;
        $lastIndex            = strlen($prefix) - 1;
        $lastChar             = $prefix[$lastIndex];
        $nextAsciiCode        = ord($lastChar) + 1;
        $rangeEnd             = $prefix;
        $rangeEnd[$lastIndex] = chr($nextAsciiCode);
        $body                 = [
            'create_request' => [
                'key'       => base64_encode($prefix),
                'range_end' => base64_encode($rangeEnd),
            ],
        ];
        $body                 = json_encode($body);
        $length               = strlen($body);
        $request              = <<<EOF
POST /v3/watch HTTP/1.1
Host: localhost
Accept: */*
User-Agent: curl/7.64.1
Content-Length: $length
Content-Type: application/json

$body
EOF;
        $client->send($request);
        while (true) {
            $data = $client->recv(-1);
            if ($data === false || $data === "") {
                return;
            }
            if (strpos($data, 'result') === false) {
                continue;
            }
            $array = json_decode($data, true);
            if (empty($array)) {
                continue;
            }
            call_user_func($this->func, $array);
        }
    }

    /**
     * Close
     */
    public function close()
    {
        $this->closed = true;
        $this->client->close();
    }

}
