<?php

namespace Mix\Micro\Etcd\Client;

use Mix\Time\Time;

/**
 * Class Watcher
 * @package Mix\Micro\Etcd\Client
 */
class Watcher
{

    /**
     * @var string
     */
    public $url;

    /**
     * @var Client
     */
    public $client;

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
     * @var bool
     */
    protected $watching = false;

    /**
     * @var \Swoole\Coroutine\Client
     */
    protected $swooleClient;

    /**
     * Connect timeout
     * @var float
     */
    protected $timeout = 5.0;

    /**
     * Watcher constructor.
     * @param string $server
     * @param Client $client
     * @param string $prefix
     * @param \Closure $func
     * @param float $timeout
     */
    public function __construct(string $url, Client $client, string $prefix, \Closure $func, float $timeout = 5.0)
    {
        $this->url     = $url;
        $this->client  = $client;
        $this->prefix  = $prefix;
        $this->func    = $func;
        $this->timeout = $timeout;
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
        $segments = parse_url($this->url);
        if (!$client->connect($segments['host'], $segments['port'], $this->timeout)) {
            throw new \Swoole\Exception(sprintf("Etcd client connect failed, %s (%s)", $client->errMsg, $server), $client->errCode);
        }
        return $client;
    }

    /**
     * Print error
     * @param \Throwable $ex
     */
    protected static function error(\Throwable $ex)
    {
        $time    = date('Y-m-d H:i:s');
        $message = sprintf('%s [%d] %s line %s', $ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine());
        echo "[error] $time $message\n";
    }

    /**
     * Watch forever
     */
    public function forever()
    {
        $this->watching = true;
        xgo(function () {
            while (true) {
                if ($this->closed) {
                    return;
                }
                try {
                    $this->watch();
                } catch (\Throwable $ex) {
                    static::error($ex);
                    sleep(1);
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
        $client               = $this->swooleClient = $this->createClient();
        $token                = $this->client->getToken();
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
Grpc-Metadata-Token: $token
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
        $client       = &$this->swooleClient;
        $this->closed = true;
        if (!$this->watching) {
            return;
        }
        if (!isset($client)) {
            // 等待 go 执行一会，当 close 在刚 forever 执行后就被立即调用的时候
            $ticker = Time::newTicker(500 * Time::MILLISECOND);
            xgo(function () use ($ticker, &$client) {
                $count = 0;
                while (true) {
                    $ticker->channel()->pop();
                    if (isset($client)) {
                        $client->close();
                        $ticker->stop();
                        return;
                    }
                    if ($count >= 6) {
                        $ticker->stop();
                        return;
                    }
                    $count++;
                }
            });
            return;
        }
        $client->close();
    }

}
