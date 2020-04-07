<?php

namespace Mix\Zipkin\Middleware\JsonRpc;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Middleware\RequestHandler;
use Mix\Zipkin\Tracer;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingClientMiddleware
 * @package Mix\Zipkin\Middleware\JsonRpc
 */
class TracingClientMiddleware implements MiddlewareInterface
{

    /**
     * @var Tracer
     */
    public $tracer;

    /**
     * TracingClientInterceptor constructor.
     * @param Tracer $tracer
     */
    public function __construct(Tracer $tracer)
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

        $operationName = 'RPC:Client:Process';
        $scope         = $tracer->startActiveSpan($operationName, [
            'tags' => $tags,
        ]);

        $headers = [];
        $tracer->inject($scope->getSpan()->getContext(), TEXT_MAP, $headers);
        // 在第一个请求的最后一个参数追加trace信息
        $request = $requests[0];
        array_push($request->params, $headers);
        
        $result = $handler->handle($request, $response);

        $scope->close();

        return $result;
    }

}