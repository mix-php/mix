<?php

namespace Mix\Http\Server;

use Mix\Http\Message\Factory\ResponseFactory;
use Mix\Http\Message\Factory\ServerRequestFactory;
use Mix\Http\Server\Event\HandledEvent;
use Mix\Http\Server\Helper\ServerHelper;
use Mix\Micro\Server\ServerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Class Server
 * @package Mix\Http\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class Server implements ServerInterface
{

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var int
     */
    public $port = 0;

    /**
     * @var bool
     */
    public $ssl = false;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

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
     * Host
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * Port
     * @return int
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * 获取全部 service 名称
     * @return string[][] [name => [class,...]]
     */
    public function services()
    {
        return [];
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
     * @param ServerHandlerInterface|null $handler
     * @throws \Swoole\Exception
     */
    public function start(ServerHandlerInterface $handler = null)
    {
        if (!is_null($handler)) {
            $this->handle('/', [$handler, 'handleHTTP']);
        }
        $server     = $this->swooleServer = new \Swoole\Coroutine\Http\Server($this->host, $this->port, $this->ssl, $this->reusePort);
        $this->port = $server->port; // 当随机分配端口时同步端口信息
        $server->set($this->options);
        foreach ($this->callbacks as $pattern => $callback) {
            $server->handle($pattern, function (Request $swooleRequest, Response $swooleResponse) use ($callback) {
                $request   = (new ServerRequestFactory)->createServerRequestFromSwoole($swooleRequest);
                $response  = (new ResponseFactory)->createResponseFromSwoole($swooleResponse);
                $microtime = ServerHelper::microtime();
                $error     = null;
                try {
                    // 执行回调
                    call_user_func($callback, $request, $response);
                } catch (\Throwable $ex) {
                    $message = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
                    $code    = $ex->getCode();
                    $error   = sprintf('[%d] %s', $code, $message);
                    // 错误处理
                    $isMix = class_exists(\Mix::class);
                    if (!$isMix) {
                        throw $ex;
                    }
                    /** @var \Mix\Console\Error $errorHandler */
                    $errorHandler = \Mix::$app->context->get('error');
                    $errorHandler->handleException($ex);
                } finally {
                    $this->dispatch($request, $response, $microtime, $error);
                }
            });
        }
        if (!$server->start()) {
            throw new \Swoole\Exception($server->errMsg, $server->errCode);
        }
    }

    /**
     * Dispatch
     * @param $request
     * @param $response
     * @param float $microtime
     * @param string|null $error
     */
    protected function dispatch($request, $response, float $microtime, string $error = null)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $event           = new HandledEvent();
        $event->time     = round((ServerHelper::microtime() - $microtime) * 1000, 2);
        $event->request  = $request;
        $event->response = $response;
        $event->error    = $error;
        $this->dispatcher->dispatch($event);
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
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

    /**
     * Create file server
     * @param string $dir
     * @param string $stripPrefix
     * @return FileServer
     */
    public static function fileServer(string $dir, string $stripPrefix = ''): FileServer
    {
        return new FileServer($dir, $stripPrefix);
    }

}
