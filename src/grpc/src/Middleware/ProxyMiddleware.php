<?php

namespace Mix\Grpc\Middleware;

use Mix\Grpc\Exception\NotFoundException;
use Mix\Grpc\Helper\GrpcHelper;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Stream\ContentStream;
use Mix\Http\Server\Middleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ProxyMiddleware
 * @package Mix\Grpc\Middleware
 */
class ProxyMiddleware implements MiddlewareInterface
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
     * Json to Protobuf
     * @param string $json
     * @param string $microService
     * @param string $microMethod
     * @return string
     * @throws \ReflectionException
     */
    protected static function jsonToProtobuf(string $json, string $microService, string $microMethod): string
    {
        $endpoint = sprintf('%s.%s', $microService, $microMethod);
        $slice    = explode('.', $endpoint);
        foreach ($slice as $key => $value) {
            $slice[$key] = ucfirst($value);
        }
        $method = array_pop($slice);
        $class  = implode('\\', $slice);

        $reflectClass     = new \ReflectionClass(sprintf('%sInterface', $class));
        $reflectMethod    = $reflectClass->getMethod($method);
        $reflectParameter = $reflectMethod->getParameters()[1];

        /** @var \Google\Protobuf\Internal\Message $instance */
        $instance = $reflectParameter->getClass()->newInstance();
        $instance->mergeFromJsonString($json);
        return $instance->serializeToString();
    }

    /**
     * Protobuf to Json
     * @param string protobuf
     * @param string $microService
     * @param string $microMethod
     * @return string
     * @throws \ReflectionException
     */
    protected static function protobufToJson(string $protobuf, string $microService, string $microMethod): string
    {
        $endpoint = sprintf('%s.%s', $microService, $microMethod);
        $slice    = explode('.', $endpoint);
        foreach ($slice as $key => $value) {
            $slice[$key] = ucfirst($value);
        }
        $method = array_pop($slice);
        $class  = implode('\\', $slice);

        $reflectClass           = new \ReflectionClass(sprintf('%sInterface', $class));
        $reflectMethod          = $reflectClass->getMethod($method);
        $reflectReturnType      = $reflectMethod->getReturnType();
        $class                  = $reflectReturnType->getName();
        $reflectReturnTypeClass = new \ReflectionClass($class);

        /** @var \Google\Protobuf\Internal\Message $instance */
        $instance = $reflectReturnTypeClass->newInstance();
        $instance->mergeFromString($protobuf);
        return $instance->serializeToJsonString();
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** 支持 go-micro web or api 的 /rpc 代理 **/
        $path        = $request->getUri()->getPath();
        $contentType = $request->getHeaderLine('Content-Type');

        // v1
        // uri: /
        // web headers: micro-endpoint micro-id micro-method micro-service
        // api headers: micro-endpoint micro-from-service micro-id micro-method micro-service
        $isV1 = $contentType == 'application/json' && $path == '/' ? true : false;
        if ($isV1) {
            $microService = $request->getHeaderLine('micro-service');
            $microMethod  = $request->getHeaderLine('micro-method');
        }

        // v2
        // uri: /php.micro.grpc.greeter.Say/Hello
        $isV2 = $contentType == 'application/grpc+json' && $path !== '/' ? true : false;
        if ($isV2) {
            $slice        = array_filter(explode('/', $path));
            $service      = explode('.', array_shift($slice));
            $method[]     = array_pop($service);
            $method[]     = array_pop($slice);
            $microService = implode('.', $service);
            $microMethod  = implode('.', $method);
            $request->withBody(new ContentStream(GrpcHelper::unpack($request->getBody()->getContents())));
        }

        // handle
        if ($isV1 || $isV2) {
            try {
                $protobuf = static::jsonToProtobuf($request->getBody()->getContents(), $microService, $microMethod);
            } catch (\ReflectionException $ex) {
                throw new NotFoundException('Micro not found');
            }
            $stream = (new StreamFactory())->createStream(GrpcHelper::pack($protobuf));
            $uri    = $request->getUri();
            $uri->withPath(sprintf('/%s.%s', $microService, str_replace('.', '/', $microMethod)));
            $request->withBody($stream);
            $request->withHeader('Content-Type', 'application/grpc');
            $request->withUri($uri);

            $response = $handler->handle($request); // 执行服务

            $protobuf = $response->getBody()->getContents();
            $json     = static::protobufToJson(GrpcHelper::unpack($protobuf), $microService, $microMethod);
            if ($isV2) {
                $json = GrpcHelper::pack($json);
            }

            $stream = (new StreamFactory())->createStream($json);
            $response->withBody($stream);
            if ($isV1) {
                $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->withHeader('Content-Type', 'application/grpc+json');
            }

            return $response;
        }

        return $handler->handle($request);
    }

}
