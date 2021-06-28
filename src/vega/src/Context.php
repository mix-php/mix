<?php

namespace Mix\Vega;

use Mix\Http\Message\Factory\ResponseFactory;
use Mix\Http\Message\Factory\ServerRequestFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Class Context
 * @package Mix\Vega
 */
class Context
{

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
     * @var \Closure[]
     */
    protected $handlers = [];

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return Context
     */
    public static function bySwoole(\Swoole\Http\Request $request, \Swoole\Http\Response $response): Context
    {
        $ctx = new static();
        $requestFactory = new ServerRequestFactory();
        $responseFactory = new ResponseFactory();
        $ctx->request = $requestFactory->createServerRequestBySwoole($request);
        $ctx->response = $responseFactory->createResponseBySwoole($response);
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
     * @throws Exception
     */
    public function next(): void
    {
        if (count($this->handlers) == 0) {
            throw new Exception('There is no handler that can be executed');
        }
        $handler = array_pop($this->handlers);
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
