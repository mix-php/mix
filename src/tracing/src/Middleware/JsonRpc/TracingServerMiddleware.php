<?php

namespace Mix\Tracing\Middleware\JsonRpc;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Middleware\RequestHandler;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingServerMiddleware
 * @package Mix\Tracing\Middleware\JsonRpc
 */
abstract class TracingServerMiddleware implements MiddlewareInterface
{

    /**
     * Get tracer
     * @return \OpenTracing\Tracer
     */
    abstract public function tracer();

    /**
     * Process
     * @param Request $requests
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $tracer = $this->tracer();
        $request->context->withValue('__tracer__', $tracer);
        
        $tags['method'] = $request->method;
        $tags['id']     = (string)$request->id;

        // 在请求的最后一个参数提取trace信息
        $params       = $request->params;
        $traceHeaders = [];
        if (is_array($params)) {
            $traceHeaders = array_pop($params);
            $traceHeaders = is_object($traceHeaders) ? (array)$traceHeaders : [];
        }

        $spanContext   = $tracer->extract(TEXT_MAP, $traceHeaders);
        $operationName = 'jsonrpc:server';
        $span          = $tracer->startSpan($operationName, [
            'child_of' => $spanContext,
            'tags'     => $tags,
        ]);

        try {
            $result = $handler->handle($request);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            $span->finish();
            $tracer->flush();
        }

        return $result;
    }

}
