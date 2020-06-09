<?php

namespace Mix\Micro\Etcd\Factory;

use Mix\Micro\Etcd\Service\Service;
use Mix\Micro\Register\Helper\ServiceHelper;
use Mix\Micro\Route\RouterInterface;
use Mix\Micro\Server\ServerInterface;

/**
 * Class ServiceFactory
 * @package Mix\Micro\Etcd\Factory
 */
class ServiceFactory
{

    /**
     * Create service
     * @param string $name
     * @param string|null $version
     * @return Service
     */
    public function createService(string $name, string $version = null): Service
    {
        if (is_null($version)) {
            $version = 'latest';
        }
        return new Service($name, $version);
    }

    /**
     * Create service bundle form http
     * @param string $namespace
     * @param ServerInterface $server
     * @param RouterInterface $router
     * @param string|null $version
     * @param array $metadata
     * @return ServiceInterface[]
     */
    public function createServiceFromHTTP(string $namespace = 'php.micro.api', ServerInterface $server, RouterInterface $router, string $version = null, array $metadata = [])
    {
        $serviceFactory  = new ServiceFactory();
        $endpointFactory = new EndpointFactory();
        $nodeFactory     = new NodeFactory();
        $services        = [];
        foreach ($router->services() as $name => $patterns) {
            $name    = sprintf('%s.%s', $namespace, $name);
            $service = $serviceFactory->createService($name, $version);
            foreach ($patterns as $pattern) {
                $endpoint = $endpointFactory->createEndpoint($pattern);
                $service->withEndpoint($endpoint);
            }
            $node = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port()));
            $node->withMetadata('registry', 'etcd');
            foreach ($metadata as $key => $value) {
                $node->withMetadata($key, $value);
            }
            $service->withNode($node);
            $services[] = $service;
        }
        return $services;
    }

    /**
     * Create service bundle form json-rpc
     * @param ServerInterface $server
     * @param string|null $version
     * @param array $metadata
     * @return ServiceInterface[]
     */
    public function createServiceFromGrpc(ServerInterface $server, string $version = null, array $metadata = [])
    {
        $serviceFactory  = new ServiceFactory();
        $endpointFactory = new EndpointFactory();
        $nodeFactory     = new NodeFactory();
        $requestFactory  = new RequestFactory();
        $responseFactory = new ResponseFactory();
        $services        = [];
        foreach ($server->services() as $name => $classes) {
            $service = $serviceFactory->createService($name, $version);
            foreach ($classes as $class) {
                $methods = get_class_methods($class);
                foreach ($methods as $method) {
                    if (strpos($method, '_') === 0) {
                        continue;
                    }

                    if (!$class::NAME) {
                        throw new \InvalidArgumentException(sprintf('Const %s::NAME can\'t be empty', $class));
                    }
                    $slice     = explode('.', $class::NAME);
                    $className = array_pop($slice);
                    $endpoint  = $endpointFactory->createEndpoint(sprintf('%s.%s', $className, $method));

                    $reflectClass     = new \ReflectionClass($class);
                    $reflectMethod    = $reflectClass->getMethod($method);
                    $reflectParameter = $reflectMethod->getParameters()[1];
                    $request          = $requestFactory->createRequest($reflectParameter);
                    $endpoint->withRequest($request);

                    $reflectClass = new \ReflectionClass($reflectMethod->getReturnType()->getName());
                    $response     = $responseFactory->createResponse($reflectClass);
                    $endpoint->withResponse($response);

                    $service->withEndpoint($endpoint);
                }
            }
            $node = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port()));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'grpc');
            $node->withMetadata('server', 'grpc');
            $node->withMetadata('transport', 'grpc');
            $service->withNode($node);
            $services[] = $service;
        }
        return $services;
    }

    /**
     * Create service bundle form json-rpc
     * @param ServerInterface $server
     * @param string|null $version
     * @param array $metadata
     * @return ServiceInterface[]
     */
    public function createServiceFromJsonRpc(ServerInterface $server, string $version = null, array $metadata = [])
    {
        $serviceFactory  = new ServiceFactory();
        $endpointFactory = new EndpointFactory();
        $nodeFactory     = new NodeFactory();
        $services        = [];
        foreach ($server->services() as $name => $classes) {
            $service = $serviceFactory->createService($name, $version);
            foreach ($classes as $class) {
                $methods = get_class_methods($class);
                foreach ($methods as $method) {
                    if (strpos($method, '_') === 0) {
                        continue;
                    }

                    if (!$class::NAME) {
                        throw new \InvalidArgumentException(sprintf('Const %s::NAME can\'t be empty', $class));
                    }
                    $slice     = explode('.', $class::NAME);
                    $className = array_pop($slice);

                    $endpoint = $endpointFactory->createEndpoint(sprintf('%s.%s', $className, $method));
                    $service->withEndpoint($endpoint);
                }
            }
            $node = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port()));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'json');
            $node->withMetadata('server', 'json');
            $node->withMetadata('transport', 'tcp');
            $service->withNode($node);
            $services[] = $service;
        }
        return $services;
    }

}
