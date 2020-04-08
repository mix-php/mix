<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\JsonRpc\Client\Dialer;
use Mix\JsonRpc\Factory\ResponseFactory;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\Micro\Gateway\Exception\ProxyException;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Micro\Gateway\ProxyInterface;
use Mix\Micro\Register\RegistryInterface;
use Mix\Micro\Register\ServiceInterface;

/**
 * Class JsonRpcProxy
 * @package Mix\Micro\Gateway\Proxy
 */
class JsonRpcProxy implements ProxyInterface
{

    /**
     * @var string
     */
    public $pattern = '/jsonrpc';

    /**
     * @var float
     */
    public $timeout = 5.0;

    /**
     * @var float
     */
    public $callTimeout = 10.0;

    /**
     * JsonRpcProxy constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Get handle pattern
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * Get service
     *
     * @param RegistryInterface $registry
     * @param ServerRequest $request
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function service(RegistryInterface $registry, ServerRequest $request)
    {
        $body = $request->getParsedBody();
        if (empty($body)) {
            throw new ProxyException('Parse request failed');
        }
        $name = $body['service'] ?? '';
        return $registry->service($name);
    }

    /**
     * Proxy
     * @param ServiceInterface $service
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws ProxyException
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false) {
            throw new ProxyException('Invalid content type');
        }
        $body    = $request->getParsedBody();
        $content = $body['request'] ?? null;
        try {
            list($single, $requests) = JsonRpcHelper::parseRequestsFromProxy($request, $content);
        } catch (\Throwable $ex) {
            $rpcResponse = (new ResponseFactory)->createErrorResponse(-32700, 'Parse error', null);
            $body        = (new StreamFactory())->createStream(json_encode($rpcResponse));
            $response
                ->withContentType('application/json', 'utf-8')
                ->withBody($body)
                ->withStatus(200);
            return $response;
        }

        $dialer = new Dialer([
            'timeout'     => $this->timeout,
            'callTimeout' => $this->callTimeout,
        ]);
        try {
            $conn = $dialer->dial($service->getAddress(), $service->getPort());

            if ($single) {
                $rpcResponse = $conn->call(array_pop($requests));
                $body        = (new StreamFactory())->createStream(json_encode($rpcResponse));
                $response
                    ->withContentType('application/json', 'utf-8')
                    ->withBody($body)
                    ->withStatus(200);
                return $response;
            }

            $rpcResponses = $conn->callMultiple(...$requests);
            $body         = (new StreamFactory())->createStream(json_encode($rpcResponses));
            $response
                ->withContentType('application/json', 'utf-8')
                ->withBody($body)
                ->withStatus(200);
            return $response;
        } catch (\Throwable $ex) {
            throw new ProxyException($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return void
     */
    public function show404(\Throwable $exception, Response $response)
    {
        $content = [
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'status'  => '404 Not Found',
        ];
        $body    = (new StreamFactory())->createStream(json_encode($content));
        $status  = 404;
        $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus($status)
            ->end();
    }

    /**
     * 500 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return void
     */
    public function show500(\Throwable $exception, Response $response)
    {
        $content = [
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'status'  => '500 Internal Server Error',
        ];
        $body    = (new StreamFactory())->createStream(json_encode($content));
        $status  = 500;
        $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus($status)
            ->end();
    }

    /**
     * Close
     */
    public function close()
    {
    }

}
