<?php

namespace Mix\Vega;

use Mix\Vega\Exception\RuntimeException;
use Mix\View\Renderer;

/**
 * Class Engine
 * @package Mix\Vega
 */
class Engine
{

    const NORMAL_MODE = 1;
    const FAST_MODE = 2;

    /**
     * @var int
     */
    protected $mode = self::NORMAL_MODE;

    use Router;
    use StaticFile;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Renderer
     */
    protected $htmlRender;

    /**
     * Engine constructor.
     */
    public function __construct()
    {
        $this->withHTMLRoot(__DIR__);
    }

    /**
     * @param int $mode
     */
    public function mode(int $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param \Closure|null $init Deprecated
     * @return \Closure
     */
    public function handler(?\Closure $init = null): \Closure
    {
        $this->startDispatcher();

        return function (...$args) use ($init) {
            // Deprecated
            static $ok = false;
            if (!$ok && $init) {
                // 即便提前锁定，在协程中依然无法防止请求击穿后因没有init导致的异常
                // 结论：init还是只能放到进程启动事件中处理
                $ok = true;
                $init();
            }

            if (static::isSwoole($args)) {
                /**
                 * @var $request \Swoole\Http\Request
                 * @var $response \Swoole\Http\Response
                 */
                list($request, $response) = $args;
                $ctx = Context::fromSwoole($this->mode, $request, $response, $this->htmlRender);
                $this->dispatch($request->server['request_method'], $request->server['path_info'] ?: '/', $ctx);
            } elseif (static::isWorkerMan($args)) {
                /**
                 * @var $connection \Workerman\Connection\TcpConnection
                 * @var $request \Workerman\Protocols\Http\Request
                 */
                list($connection, $request) = $args;
                $ctx = Context::fromWorkerMan($this->mode, $request, $connection, $this->htmlRender);
                $this->dispatch($request->method(), $request->path(), $ctx);
            } elseif (static::isSwow($args)) {
                /**
                 * @var $request \Swow\Http\Server\Request
                 * @var $response \Swow\Http\Server\Connection
                 */
                list($request, $response) = $args;
                $ctx = Context::fromSwow($this->mode, $request, $response, $this->htmlRender);
                $this->dispatch($request->getMethod(), $request->getUri()->getPath(), $ctx);
            } else {
                throw new RuntimeException('The current usage scenario is not supported');
            }
        };
    }

    /**
     * 立即执行
     * 支持 PHP-FPM, cli-server
     * @return bool
     */
    public function run(): bool
    {
        $this->startDispatcher();

        $ctx = Context::fromFPM($this->htmlRender); // check cannot be used in CLI mode
        if (PHP_SAPI == 'cli-server') {
            // php -S localhost:8000 index.php 不带PATH_INFO
            // php -S localhost:8000 -t public/ index.php 不带PATH_INFO
            // php -S localhost:8000 -t public/ 带PATH_INFO, 但是/不带
            $uri = $_SERVER['SCRIPT_NAME'];
            if (isset($_SERVER['PATH_INFO'])) {
                $uri = $_SERVER['PATH_INFO'];
            } elseif ($_SERVER['SCRIPT_NAME'] == '/index.php') {
                $uri = '/';
            }
        } else {
            // PHP-FPM
            $uriPath = function () {
                $uri = explode('?', $_SERVER['REQUEST_URI']);
                return '/' . trim($uri[0], '/');
            };
            $uri = isset($_SERVER['PATH_INFO']) ? ($_SERVER['PATH_INFO'] ?: '/') : $uriPath();
        }
        $this->dispatch($_SERVER['REQUEST_METHOD'], $uri, $ctx);

        // 支持cli-server静态文件
        return empty($GLOBALS['__sendfile__']);
    }

    /**
     * @param array $args
     * @return bool
     */
    private static function isSwoole(array $args): bool
    {
        if (count($args) != 2) {
            return false;
        }
        list($request, $response) = $args;
        if ($request instanceof \Swoole\Http\Request && $response instanceof \Swoole\Http\Response) {
            return true;
        }
        return false;
    }

    /**
     * @param array $args
     * @return bool
     */
    private static function isWorkerMan(array $args): bool
    {
        if (count($args) != 2) {
            return false;
        }
        list($connection, $request) = $args;
        if ($connection instanceof \Workerman\Connection\TcpConnection && $request instanceof \Workerman\Protocols\Http\Request) {
            return true;
        }
        return false;
    }

    /**
     * @param array $args
     * @return bool
     */
    private static function isSwow(array $args): bool
    {
        if (count($args) != 2) {
            return false;
        }
        list($request, $response) = $args;
        if ($request instanceof \Swow\Http\Server\Request && $response instanceof \Swow\Http\Server\Connection) {
            return true;
        }
        return false;
    }

    /**
     * @param string $root
     */
    public function withHTMLRoot(string $root)
    {
        $this->htmlRender = new Renderer($root);
    }

}
