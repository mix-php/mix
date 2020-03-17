<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\JsonRpc\Client\Dialer;
use Mix\JsonRpc\Factory\RequestFactory;
use Mix\JsonRpc\Factory\ResponseFactory;
use Mix\JsonRpc\Helper\JsonRpcHelper;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\Gateway\ProxyInterface;
use Mix\Micro\RegistryInterface;
use Mix\Micro\ServiceInterface;
use Mix\WebSocket\Upgrader;

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
     * @var string
     */
    public $namespace = 'php.micro.jsonrpc';

    /**
     * @var float
     */
    public $timeout = 5.0;

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
        $name = $body['service'] ?? '';
        return $registry->get($name);
    }

    /**
     * Proxy
     * @param ServiceInterface $service
     * @param ServerRequest $request
     * @param Response $response
     * @return int status
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response)
    {
        // 参数效验
        $content = $request->getBody()->getContents();
        try {
            list(, $requests) = JsonRpcHelper::parseRequests($content);
        } catch (\Throwable $ex) {
            $response = (new ResponseFactory)->createErrorResponse(-32700, 'Parse error', null);
            $body     = (new StreamFactory())->createStream(json_encode($rpcResponse));
            $response
                ->withContentType('application/json', 'utf-8')
                ->withBody($body)
                ->withStatus(200)
                ->end();
            return;
        }
        $rpcRequest = array_pop($requests);
        if (!JsonRpcHelper::validRequest($rpcRequest)) {
            $rpcResponse = (new ResponseFactory)->createErrorResponse(-32600, 'Invalid Request', $request->id);
            $body        = (new StreamFactory())->createStream(json_encode($rpcResponse));
            $response
                ->withContentType('application/json', 'utf-8')
                ->withBody($body)
                ->withStatus(200)
                ->end();
            return;
        }

        $dialer = new Dialer([
            'timeout' => $this->timeout,
        ]);
        try {
            $conn        = $dialer->dial($service->getAddress(), $service->getPort());
            $rpcResponse = $conn->call($rpcRequest);
        } catch (\Throwable $ex) {
            $rpcResponse = (new  ResponseFactory())->createErrorResponse($ex->getCode(), $ex->getMessage(), $id);
        }
        $body = (new StreamFactory())->createStream(json_encode($rpcResponse));
        $response
            ->withContentType('application/json', 'utf-8')
            ->withBody($body)
            ->withStatus(200)
            ->end();
    }

    /**
     * Close
     */
    public function close()
    {
    }

}
