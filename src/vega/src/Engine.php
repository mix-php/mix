<?php

namespace Mix\Vega;

use FastRoute\Dispatcher;

/**
 * Class Engine
 * @package Mix\Vega
 */
class Engine implements RouterInterface
{

    use RouterTrait;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @return \Closure
     */
    public function handler(): \Closure
    {
        $this->startDispatcher();

        return function (...$args) {
            if (static::isSwoole($args)) {
                /**
                 * @var $request \Swoole\Http\Request
                 * @var $response \Swoole\Http\Response
                 */
                list($request, $response) = $args;
                $ctx = Context::bySwoole($request, $response);
                $this->dispatch($request->getMethod(), $request->getServerParams()['path_info'] ?: '/', $ctx);
            } elseif (static::isWorkerMan($args)) {

            } else {
                throw new Exception('The current usage scenario is not supported');
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
        list($connection, $data) = $args;
        if ($connection instanceof \Workerman\Connection\TcpConnection && $data instanceof \Workerman\Protocols\Http\Request) {
            return true;
        }
        return false;
    }

}
