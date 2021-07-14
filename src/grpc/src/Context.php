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
    public function setHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
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
