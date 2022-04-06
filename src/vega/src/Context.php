<?php

namespace Mix\Vega;

use Mix\Http\Message\Factory\ResponseFactory;
use Mix\Http\Message\Factory\ServerRequestFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Vega\Exception\RuntimeException;
use Mix\View\Renderer;

/**
 * Class Context
 * @package Mix\Vega
 */
class Context
{

    use Store;
    use Input, Writer {
        Input::defaultQuery insteadof Writer;
    }

    /**
     * @var Response
     */
    public $response;

    /**
     * @var ServerRequest|\Swoole\Http\Request|\Workerman\Protocols\Http\Request
     */
    public $request;

    /**
     * @var Renderer
     */
    public $renderer;

    /**
     * @var \Closure[]
     */
    protected $handlers = [];

    /**
     * @param int $mode
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @param Renderer $renderer
     * @return Context
     */
    public static function fromSwoole(int $mode, \Swoole\Http\Request $request, \Swoole\Http\Response $response, Renderer $renderer): Context
    {
        $ctx = new static();
        switch ($mode) {
            case Engine::FAST_MODE:
                $ctx->request = $request;
                break;
            default:
                $ctx->request = (new ServerRequestFactory())->createServerRequestFromSwoole($request);
        }
        $ctx->response = (new ResponseFactory())->createResponseFromSwoole($response);
        $ctx->renderer = $renderer;
        return $ctx;
    }


    public static function fromSwow(int $mode, \Swow\Http\Server\Request $request, \Swow\Http\Server\Connection $response, Renderer $renderer): Context
    {
        $ctx = new static();
        switch ($mode) {
            case Engine::FAST_MODE:
                $ctx->request = $request;
                break;
            default:
                $ctx->request = (new ServerRequestFactory())->createServerRequestFromSwow($request);
        }
        $ctx->response = (new ResponseFactory())->createResponseFromSwow($response);
        $ctx->renderer = $renderer;
        return $ctx;
    }

    /**
     * @param int $mode
     * @param \Workerman\Protocols\Http\Request $request
     * @param \Workerman\Connection\TcpConnection $connection
     * @param Renderer $renderer
     * @return Context
     */
    public static function fromWorkerMan(int $mode, \Workerman\Protocols\Http\Request $request, \Workerman\Connection\TcpConnection $connection, Renderer $renderer): Context
    {
        $ctx = new static();
        switch ($mode) {
            case Engine::FAST_MODE:
                $ctx->request = $request;
                break;
            default:
                $ctx->request = (new ServerRequestFactory())->createServerRequestFromWorkerMan($request);
        }
        $ctx->response = (new ResponseFactory())->createResponseFromWorkerMan($connection);
        $ctx->renderer = $renderer;
        return $ctx;
    }

    /**
     * @param Renderer $renderer
     * @return Context
     */
    public static function fromFPM(Renderer $renderer): Context
    {
        $ctx = new static();
        $ctx->request = (new ServerRequestFactory())->createServerRequestFromFPM();
        $ctx->response = (new ResponseFactory())->createResponseFromFPM();
        $ctx->renderer = $renderer;
        return $ctx;
    }

    /**
     */
    public function abort(): void
    {
        throw new Abort();
    }

    /**
     * @param int $code
     */
    public function abortWithStatus(int $code): void
    {
        throw new Abort('', $code);
    }

    /**
     * @param int $code
     */
    public function abortWithStatusJSON(int $code, $data): void
    {
        throw new Abort(static::jsonMarshal($data), $code);
    }

    /**
     * @param int $code
     */
    public function abortWithStatusException(int $code, \Throwable $ex): void
    {
        throw new Abort($ex->getMessage(), $code);
    }

    /**
     * @throws RuntimeException
     */
    public function next(): void
    {
        if (count($this->handlers) == 0) {
            throw new RuntimeException('There is no handler that can be executed');
        }
        $handler = array_shift($this->handlers);
        $handler($this);
    }

    /**
     * @param array $handlers
     */
    public function withHandlers(array $handlers)
    {
        $this->handlers = $handlers;
    }

}
