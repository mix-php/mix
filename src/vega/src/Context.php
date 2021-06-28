<?php

namespace Mix\Vega;

use Mix\Http\Message\Factory\ResponseFactory;
use Mix\Http\Message\Factory\ServerRequestFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Stream\StringStream;

/**
 * Class Context
 * @package Mix\Vega
 */
class Context
{

    /**
     * @var ServerRequest
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

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

    public function string(string $content)
    {
        $body = new StringStream($content);
        $this->response->withBody($body);
        $this->response->send();
    }

    /**
     * @throws Abort
     */
    public function abort(): void
    {
        throw new Abort();
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
        $handler();
    }

    /**
     * @param array $handlers
     */
    public function withHandlers(array $handlers)
    {
        $this->handlers = $handlers;
    }

}
