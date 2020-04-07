<?php

namespace Mix\Zipkin\Middleware\Http;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareInterface;
use Mix\Zipkin\Tracer;
use Mix\Zipkin\Tracing;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingServerMiddleware
 * @package Mix\Zipkin\Middleware\Http
 */
class TracingServerMiddleware implements MiddlewareInterface
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
     * @return Tracer
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

        $headers       = $request->getHeaderLines();
        $spanContext   = $tracer->extract(TEXT_MAP, $headers);
        $operationName = $request->getUri()->getPath();
        $span          = $tracer->startSpan($operationName, [
            'child_of' => $spanContext,
            'tags'     => [
                'method' => $request->getMethod(),
                'uri'    => $request->getUri()->__toString(),
            ],
        ]);

        $context           = $request->getContext();
        $context['tracer'] = $tracer;

        $result = $handler->handle($request);

        $span->finish();
        $tracer->flush();

        return $result;
    }

}
