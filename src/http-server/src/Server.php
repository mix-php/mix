<?php

namespace Mix\Http\Server;

use Mix\Http\Message\Factory\ResponseFactory;
use Mix\Http\Message\Factory\ServerRequestFactory;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Class Server
 * @package Mix\Http\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class Server
{

    /**
     * @var string
     */
    public $host = '127.0.0.1';

    /**
     * @var int
     */
    public $port = 9501;

    /**
     * @var bool
     */
    public $ssl = false;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var []callable
     */
    protected $callbacks = [];

    /**
     * @var \Swoole\Coroutine\Http\Server
     */
    public $swooleServer;

    /**
     * HttpServer constructor.
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @param bool $reusePort
     */
    public function __construct(string $host, int $port, bool $ssl = false, bool $reusePort = false)
    {
        $this->host      = $host;
        $this->port      = $port;
        $this->ssl       = $ssl;
        $this->reusePort = $reusePort;
    }

    /**
     * Set
     * @param array $options
     */
    public function set(array $options)
    {
        $this->options = $options;
    }

    /**
     * Handle
     * @param string $pattern
     * @param callable $callback
     */
    public function handle(string $pattern, callable $callback)
    {
        $this->callbacks[$pattern] = $callback;
    }

    /**
     * Start
     * @param HandlerInterface|null $handler
     * @throws \Swoole\Exception
     */
    public function start(HandlerInterface $handler = null)
    {
        if (!is_null($handler)) {
            $this->callbacks = [];
            $this->handle('/', [$handler, 'HandleHTTP']);
        }
        $server = $this->swooleServer = new \Swoole\Coroutine\Http\Server($this->host, $this->port, $this->ssl, $this->reusePort);
        $server->set($this->options);
        foreach ($this->callbacks as $pattern => $callback) {
            $server->handle($pattern, function (Request $requ, Response $resp) use ($callback) {
                try {
                    // 生成PSR的request,response
                    $request  = (new ServerRequestFactory)->createServerRequestFromSwoole($requ);
                    $response = (new ResponseFactory)->createResponseFromSwoole($resp);
                    // 执行回调
                    call_user_func($callback, $request, $response);
                } catch (\Throwable $e) {
                    $isMix = class_exists(\Mix::class);
                    // 错误处理
                    if (!$isMix) {
                        throw $e;
                    }
                    // Mix错误处理
                    /** @var \Mix\Console\Error $error */
                    $error = \Mix::$app->context->get('error');
                    $error->handleException($e);
                }
            });
        }
        if (!$server->start()) {
            throw new \Swoole\Exception($server->errMsg, $server->errCode);
        }
    }

    /**
     * Shutdown
     */
    public function shutdown()
    {
        if (!$this->swooleServer) {
            return;
        }
        if (!$this->swooleServer->shutdown()) { // 返回 null
            $errMsg  = $this->swooleServer->errMsg;
            $errCode = $this->swooleServer->errCode;
            if ($errMsg == 'Operation canceled' && in_array($errCode, [89, 125])) { // mac=89, linux=125
                return;
            }
            throw new \Swoole\Exception($errMsg, $errCode);
        }
    }

}
