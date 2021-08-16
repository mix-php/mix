<?php

namespace Mix\Grpc;

use Swoole\Http\Request;
use Swoole\Http\Response;

class Context
{

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var float
     */
    protected $timeout = 5.0;

    /**
     * @param Request $request
     * @param Response $response
     * @return Context
     */
    public static function fromSwoole(Request $request, Response $response): Context
    {
        $ctx = new self();
        $ctx->request = $request;
        $ctx->response = $response;
        return $ctx;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function withHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * @param float $timeout
     */
    public function withTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeader(string $name): string
    {
        if ($this->request) {
            return $this->request->header[$name] ?? '';
        }
        return $this->headers[$name] ?? '';
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        if ($this->request) {
            return $this->request->header ?? [];
        }
        return $this->headers;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

}
