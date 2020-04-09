<?php

namespace Mix\Zipkin\Middleware\JsonRpc;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Middleware\RequestHandler;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingClientMiddleware
 * @package Mix\Zipkin\Middleware\JsonRpc
 */
class TracingClientMiddleware implements MiddlewareInterface
{

    /**
     * @var \OpenTracing\Tracer
     */
    public $tracer;

    /**
     * TracingClientInterceptor constructor.
     * @param \OpenTracing\Tracer $tracer
     */
    public function __construct(\OpenTracing\Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * Process
     * @param Request[] $requests
     * @param RequestHandler $handler
     * @return Response[] $responses
     */
    public function process(array $requests, RequestHandler $handler): array
    {
        $tracer = $this->tracer;
        $tags   = [];
        foreach ($requests as $key => $request) {
            $tags[sprintf('method-%d', $key)] = $request->method;
        }

        $operationName = 'RPC:Client';
        $scope         = $tracer->startActiveSpan($operationName, [
            'tags' => $tags,
        ]);

        $traceHeaders = [];
        $tracer->inject($scope->getSpan()->getContext(), TEXT_MAP, $traceHeaders);
        // 在第一个请求的最后一个参数追加trace信息
        $request = $requests[0];
        array_push($request->params, $traceHeaders);

        try {
            $result = $handler->handle($requests);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            $scope->close();
        }

        return $result;
    }

}