<?php

namespace Mix\Tracing\JsonRpc;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Middleware\RequestHandler;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingServerMiddleware
 * @package Mix\Tracing\JsonRpc
 */
abstract class TracingServerMiddleware implements MiddlewareInterface
{

    /**
     * Get tracer
     * @param string $serviceName
     * @return \OpenTracing\Tracer
     */
    abstract public function tracer(string $serviceName);

    /**
     * Process
     * @param Request $requests
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $serviceName = $request->method;
        $tracer      = $this->tracer($serviceName);

        // 在请求的最后一个参数提取trace信息
        $params       = $request->params;
        $traceHeaders = [];
        if (is_array($params)) {
            $traceHeaders = array_pop($params);
            $traceHeaders = is_object($traceHeaders) ? (array)$traceHeaders : [];
        }

        $spanContext   = $tracer->extract(TEXT_MAP, $traceHeaders);
        $operationName = 'jsonrpc.server';
        $span          = $tracer->startSpan($operationName, [
            'child_of' => $spanContext,
            'tags'     => [
                'method' => $request->method,
                'id'     => (string)$request->id,
            ],
        ]);

        // Tracing::extract
        $request->context->withValue('__tracer__', $tracer);

        try {
            $result = $handler->handle($request);
        } catch (\Throwable $ex) {
            throw $ex;
        } finally {
            $span->finish();
            $tracer->flush();
        }

        return $result;
    }

}
