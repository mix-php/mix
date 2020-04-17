<?php

namespace Mix\Tracing\Middleware\Grpc;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\HTTP_STATUS_CODE;

/**
 * Class TracingServerMiddleware
 * @package Mix\Tracing\Middleware\Grpc
 */
abstract class TracingServerMiddleware implements MiddlewareInterface
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
     * ActionMiddleware constructor.
     * @param ServerRequest $request
     * @param Response $response
     */
    public function __construct(ServerRequest $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * Get tracer
     * @return \OpenTracing\Tracer
     */
    abstract public function tracer();

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tracer = $this->tracer();

        $headers       = $this->request->getHeaderLines();
        $spanContext   = $tracer->extract(TEXT_MAP, $headers);
        $operationName = $request->getUri()->getPath();
        $span          = $tracer->startSpan($operationName, [
            'child_of' => $spanContext,
            'tags'     => [
                'method' => $request->getMethod(),
                'uri'    => $request->getUri()->__toString(),
            ],
        ]);

        // 让 Gateway 能把追踪信息通过 Header 代理出去 (web/websocket/api)
        $traceHeaders = [];
        $tracer->inject($span->getContext(), TEXT_MAP, $traceHeaders);
        foreach ($traceHeaders as $name => $value) {
            $request->withHeader($name, $value);
        }

        // 把 TraceID 发送至用户的 Header 中
        $traceID = $traceHeaders['x-b3-traceid'] ?? null;
        if ($traceID) {
            $this->response->withHeader('x-b3-traceid', $traceID);
        }

        // 记录 x- 开头的内部 Header 信息
        foreach ($this->request->getHeaderLines() as $key => $value) {
            if (stripos($key, 'x-') === 0 && stripos($key, 'x-b3') === false) {
                $span->setTag($key, $value);
            }
        }

        // Tracing::extract
        $context = $this->request->getContext();
        $context->withValue('__tracer__', $tracer);

        try {
            $result = $handler->handle($request);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            // 记录响应信息
            $span->setTag(HTTP_STATUS_CODE, $this->response->getStatusCode());

            $span->finish();
            $tracer->flush();
        }

        return $result;
    }

}
