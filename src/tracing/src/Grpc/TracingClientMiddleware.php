<?php

namespace Mix\Tracing\Grpc;

use Mix\Grpc\Client\Middleware\MiddlewareInterface;
use Mix\Grpc\Client\Middleware\RequestHandler;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\ERROR;

/**
 * Class TracingClientMiddleware
 * @package Mix\Tracing\Grpc
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
     * @param \Swoole\Http2\Request $parameters
     * @param RequestHandler $handler
     * @return object
     */
    public function process(\Swoole\Http2\Request $request, RequestHandler $handler): \Swoole\Http2\Response
    {
        $slice         = array_filter(explode('/', $request->path));
        $tmp           = array_filter(explode('.', array_shift($slice)));
        $serviceMethod = sprintf('%s.%s', array_pop($tmp), array_pop($slice));
        $serviceName   = implode('.', $tmp);

        $tracer = $this->tracer;

        $operationName = sprintf('%s:%s', 'grpc.client', $serviceName);
        $scope         = $tracer->startActiveSpan($operationName, [
            'tags' => [
                'service.name'    => $serviceName,
                'service.method'  => $serviceMethod,
                'service.address' => $request->headers['host'] ?? '',
            ],
        ]);

        $traceHeaders = [];
        $tracer->inject($scope->getSpan()->getContext(), TEXT_MAP, $traceHeaders);
        $request->headers = array_merge($request->headers, $traceHeaders);

        try {
            $result = $handler->handle($request);
        } catch (\Throwable $ex) {
            $message = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), $ex->getFile(), $ex->getLine());
            $code    = $ex->getCode();
            $error   = sprintf('[%d] %s', $code, $message);
            throw $ex;
        } finally {
            if (isset($error)) {
                $scope->getSpan()->setTag(ERROR, $error);
            }
            $scope->close();
        }

        return $result;
    }

}
