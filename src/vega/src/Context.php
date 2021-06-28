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

    /**
     * @param int $code
     * @param string $format
     * @param ...$values
     * @return bool
     */
    public function string(int $code, string $format, ...$values): bool
    {
        $this->response->withStatus($code);
        $body = new StringStream(sprintf($format, ...$values));
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param int $code
     * @param $data
     * @return bool
     */
    public function json(int $code, $data): bool
    {
        $body = new StringStream(static::jsonMarshal($data));
        $this->response->withContentType('application/json');
        $this->response->withStatus($code);
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param int $code
     * @param $data
     * @return bool
     */
    public function jsonp(int $code, $data): bool
    {
        $callback = $this->defaultQuery('callback', '');
        if ($callback == '') {
            return $this->json($code, $data);
        }

        $json = static::jsonMarshal($data);
        $javascript = sprintf('%s(%s)', $callback, $json);
        $body = new StringStream($javascript);
        $this->response->withContentType('application/javascript');
        $this->response->withStatus($code);
        $this->response->withBody($body);
        return $this->response->send();
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function defaultQuery(string $key, string $default): string
    {
        $value = $this->request->getQueryParams()[$key] ?? '';
        if ($value) {
            return $value;
        }
        return $default;
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

    /**
     * @param $data
     * @return string
     */
    protected static function jsonMarshal($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

}
