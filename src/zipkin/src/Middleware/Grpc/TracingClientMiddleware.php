<?php

namespace Mix\Zipkin\Middleware\Grpc;

use Mix\Grpc\Client\Message\Request;
use Mix\Grpc\Client\Middleware\MiddlewareInterface;
use Mix\Grpc\Client\Middleware\RequestHandler;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingClientMiddleware
 * @package Mix\Zipkin\Middleware\Grpc
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
     * @param Request $parameters
     * @param RequestHandler $handler
     * @return object
     */
    public function process(Request $request, RequestHandler $handler): object
    {
        $tracer = $this->tracer;

        list($object, $method) = $request->callback;
        $tags['method'] = sprintf('%s.%s', basename(str_replace('\\', '/', get_class($object))), $method);
        $operationName  = 'grpc:client';
        $scope          = $tracer->startActiveSpan($operationName, [
            'tags' => $tags,
        ]);

        $traceHeaders = [];
        $tracer->inject($scope->getSpan()->getContext(), TEXT_MAP, $traceHeaders);
        // 追加trace信息, grpc 的 metadata 符合 psr 标准, value 必须是 array 类型
        $psrHeaders = [];
        foreach ($traceHeaders as $key => $value) {
            $psrHeaders[$key] = [$value];
        }
        $parameters           = $request->parameters;
        $parameters->metadata = array_merge($parameters->metadata, $psrHeaders);

        try {
            $result = $handler->handle($request);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            $scope->close();
        }

        return $result;
    }

}
