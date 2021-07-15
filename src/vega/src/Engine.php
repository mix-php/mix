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
     * @param \Closure|null $init
     * @return \Closure
     */
    public function handler(?\Closure $init = null): \Closure
    {
        $this->startDispatcher();

        return function (...$args) use ($init) {
            static $ok = false;
            if (!$ok && $init) {
                $init();
                $ok = true;
            }

            if (static::isSwoole($args)) {
                /**
                 * @var $request \Swoole\Http\Request
                 * @var $response \Swoole\Http\Response
                 */
                list($request, $response) = $args;
                $ctx = Context::fromSwoole($request, $response, $this->htmlRender);
                $this->dispatch($request->server['request_method'], $request->server['path_info'] ?: '/', $ctx);
            } elseif (static::isWorkerMan($args)) {
                /**
                 * @var $connection \Workerman\Connection\TcpConnection
                 * @var $request \Workerman\Protocols\Http\Request
                 */
                list($connection, $request) = $args;
                $ctx = Context::fromWorkerMan($request, $connection, $this->htmlRender);
                $this->dispatch($request->method(), $request->path(), $ctx);
            } else {
                throw new RuntimeException('The current usage scenario is not supported');
            }
        };
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
     * @param string $root
     */
    public function withHTMLRoot(string $root)
    {
        $this->htmlRender = new Renderer($root);
    }

}
