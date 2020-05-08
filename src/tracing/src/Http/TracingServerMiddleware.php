<?php

namespace Mix\Tracing\Http;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\HTTP_METHOD;
use const OpenTracing\Tags\HTTP_STATUS_CODE;
use const OpenTracing\Tags\ERROR;
use const OpenTracing\Tags\HTTP_URL;

/**
 * Class TracingServerMiddleware
 * @package Mix\Tracing\Http
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
        $serviceName = $request->getUri()->getPath();
        $tracer      = $this->tracer($serviceName);

        $headers       = $this->request->getHeaderLines();
        $spanContext   = $tracer->extract(TEXT_MAP, $headers);
        $operationName = $request->getUri()->getPath();
        $span          = $tracer->startSpan($operationName, [
            'child_of' => $spanContext,
            'tags'     => [
                HTTP_METHOD => $request->getMethod(),
                HTTP_URL    => $request->getUri()->__toString(),
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
            $message = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
            $code    = $ex->getCode();
            $error   = sprintf('[%d] %s', $code, $message);
            throw $ex;
        } finally {
            $span->setTag(HTTP_STATUS_CODE, $this->response->getStatusCode());
            if (isset($error)) {
                $span->setTag(ERROR, $error);
            }

            $span->finish();
            $tracer->flush();
        }

        return $result;
    }

}
