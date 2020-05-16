<?php

namespace Mix\Tracing\Grpc;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\HTTP_METHOD;
use const OpenTracing\Tags\HTTP_STATUS_CODE;
use const OpenTracing\Tags\HTTP_URL;

/**
 * Class TracingServerMiddleware
 * @package Mix\Tracing\Grpc
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
     * @param string $serviceName
     * @return \OpenTracing\Tracer
     */
    abstract public function tracer(string $serviceName);

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $slice         = array_filter(explode('/', $request->getUri()->getPath()));
        $tmp           = array_filter(explode('.', array_shift($slice)));
        $serviceMethod = sprintf('%s.%s', array_pop($tmp), array_pop($slice));
        $serviceName   = implode('.', $tmp);
        $tracer        = $this->tracer($serviceName);

        $headers       = $this->request->getHeaderLines();
        $spanContext   = $tracer->extract(TEXT_MAP, $headers);
        $operationName = $request->getUri()->getPath();
        $span          = $tracer->startSpan($operationName, [
            'child_of' => $spanContext,
            'tags'     => [
                'service.name'   => $serviceName,
                'service.method' => $serviceMethod,
                HTTP_METHOD      => $request->getMethod(),
                HTTP_URL         => $request->getUri()->__toString(),
            ],
        ]);

        // 把 TraceID 发送至用户的 Header 中
        $traceHeaders = [];
        $tracer->inject($span->getContext(), TEXT_MAP, $traceHeaders);
        $traceID = $traceHeaders['x-b3-traceid'] ?? null;
        if ($traceID) {
            $this->response->withHeader('x-b3-traceid', $traceID);
        }

        // 记录 x- 开头的内部 Header 信息
        foreach ($this->request->getHeaderLines() as $key => $value) {
            if (stripos($key, 'x-') === 0 && stripos($key, 'x-b3') === false) {
                $span->setTag(sprintf('http.header.%s', $key), $value);
            }
        }

        // Tracing::extract
        $context = $this->request->getContext();
        $context->withValue('__tracer__', $tracer);

        try {
            $result = $handler->handle($request);
        } catch (\Throwable $ex) {
            throw $ex;
        } finally {
            // 记录响应信息
            $span->setTag(HTTP_STATUS_CODE, $this->response->getStatusCode());

            $span->finish();
            $tracer->flush();
        }

        return $result;
    }

}
