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
        $tracer = $this->tracer;

        $operationName = 'grpc:client';
        $scope         = $tracer->startActiveSpan($operationName, [
            'tags' => [
                'method' => $request->method(),
                'uri'    => $request->path,
            ],
        ]);

        $traceHeaders = [];
        $tracer->inject($scope->getSpan()->getContext(), TEXT_MAP, $traceHeaders);
        // 追加trace信息, grpc 的 metadata 符合 psr 标准, value 必须是 array 类型
        $psrHeaders = [];
        foreach ($traceHeaders as $key => $value) {
            $psrHeaders[$key] = [$value];
        }
        $request->headers = array_merge($request->headers, $psrHeaders);

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
