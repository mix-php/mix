<?php

namespace Mix\Zipkin\Middleware\JsonRpc;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Middleware\RequestHandler;
use Mix\Zipkin\Tracer;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingServerMiddleware
 * @package Mix\Zipkin\Middleware\JsonRpc
 */
class TracingServerMiddleware implements MiddlewareInterface
{

    /**
     * Get tracer
     * @return Tracer
     */
    abstract public function tracer();

    /**
     * Process
     * @param Request[] $requests
     * @param RequestHandler $handler
     * @return Response[] $responses
     */
    public function process(array $requests, RequestHandler $handler): array
    {
        $tracer = $this->tracer();
        $tags   = [];
        foreach ($requests as $key => $request) {
            $request->context['tracer']       = $tracer;
            $tags[sprintf('method-%d', $key)] = $request->method;
        }

        // 在第一个请求的最后一个参数提取trace信息
        $request = array_pop($requests);
        $params  = $request->params;
        $headers = [];
        if (is_array($params)) {
            $headers = array_pop($params);
            $headers = is_object($headers) ? (array)$headers : [];
        }
        
        $spanContext   = $tracer->extract(TEXT_MAP, $headers);
        $operationName = 'RPC:Server:Process';
        $span          = $tracer->startSpan($operationName, [
            'child_of' => $spanContext,
            'tags'     => $tags,
        ]);

        $result = $handler->handle($requests);

        $span->finish();
        $tracer->flush();

        return $result;
    }

}