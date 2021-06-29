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
    use Input;
    use Writer;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var ServerRequest
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
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return Context
     */
    public static function fromSwoole(\Swoole\Http\Request $request, \Swoole\Http\Response $response, Renderer $renderer): Context
    {
        $ctx = new static();
        $requestFactory = new ServerRequestFactory();
        $responseFactory = new ResponseFactory();
        $ctx->request = $requestFactory->createServerRequestFromSwoole($request);
        $ctx->response = $responseFactory->createResponseFromSwoole($response);
        $ctx->renderer = $renderer;
        return $ctx;
    }

    /**
     * @param \Workerman\Protocols\Http\Request $request
     * @param \Workerman\Connection\TcpConnection $connection
     * @return Context
     */
    public static function fromWorkerMan(\Workerman\Protocols\Http\Request $request, \Workerman\Connection\TcpConnection $connection, Renderer $renderer): Context
    {
        $ctx = new static();
        $requestFactory = new ServerRequestFactory();
        $responseFactory = new ResponseFactory();
        $ctx->request = $requestFactory->createServerRequestFromWorkerMan($request);
        $ctx->response = $responseFactory->createResponseFromWorkerMan($connection);
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
