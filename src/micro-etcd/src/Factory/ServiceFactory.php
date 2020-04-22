<?php

namespace Mix\Micro\Etcd\Factory;

use Mix\Micro\Etcd\Service\Service;
use Mix\Micro\Etcd\Service\ServiceBundle;
use Mix\Micro\Register\Helper\ServiceHelper;
use Mix\Micro\Register\ServiceFactoryInterface;
use Mix\Micro\Register\ServiceInterface;

/**
 * Class ServiceFactory
 * @package Mix\Micro\Etcd\Factory
 */
class ServiceFactory implements ServiceFactoryInterface
{

    /**
     * Create service
     * @param string $name
     * @param string|null $version
     * @return ServiceInterface
     */
    public function createService(string $name, ?string $version = null): ServiceInterface
    {
        if (is_null($version)) {
            $version = date('Y.m.d.H.i');
        }
        return new Service($name, $version);
    }

    /**
     * Create service bundle form api
     * @param \Mix\Http\Server\Server $server
     * @param \Mix\Route\Router $router
     * @param string $namespace
     * @param string|null $version
     * @return ServiceInterface[]
     */
    public function createServicesFromAPI(\Mix\Http\Server\Server $server, \Mix\Route\Router $router, string $namespace = 'php.micro.api', ?string $version = null)
    {
        $serviceFactory = new ServiceFactory();
        $nodeFactory    = new NodeFactory();
        $services       = [];
        foreach ($router->services() as $name) {
            $name    = sprintf('%s.%s', $namespace, $name);
            $service = $serviceFactory->createService($name, $version);
            $node    = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'json');
            $node->withMetadata('server', 'json');
            $node->withMetadata('transport', 'http');
            $service->withNode($node);
            $services[] = $service;
        }
        return $services;
    }

    /**
     * Create service bundle form web
     * @param \Mix\Http\Server\Server $server
     * @param \Mix\Route\Router $router
     * @param string $namespace
     * @param string|null $version
     * @return ServiceInterface[]
     */
    public function createServicesFromWeb(\Mix\Http\Server\Server $server, \Mix\Route\Router $router, string $namespace = 'php.micro.web', ?string $version = null)
    {
        $serviceFactory = new ServiceFactory();
        $nodeFactory    = new NodeFactory();
        $services       = [];
        foreach ($router->services() as $name) {
            $name    = sprintf('%s.%s', $namespace, $name);
            $service = $serviceFactory->createService($name, $version);
            $node    = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'html');
            $node->withMetadata('server', 'html');
            $node->withMetadata('transport', 'http');
            $service->withNode($node);
            $services[] = $service;
        }
        return $services;
    }

    /**
     * Create service bundle form json-rpc
     * @param \Mix\Grpc\Server $server
     * @param string|null $version
     * @return ServiceInterface[]
     */
    public function createServicesFromGrpc(\Mix\Grpc\Server $server, ?string $version = null)
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
            $node = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port));
            $node->withMetadata('registry', 'etcd');
            $node->withMetadata('protocol', 'grpc');
            $node->withMetadata('server', 'grpc');
            $node->withMetadata('transport', 'http');
            $service->withNode($node);
            $services[] = $service;
        }
        return $services;
    }

    /**
     * Create service bundle form json-rpc
     * @param \Mix\JsonRpc\Server $server
     * @param string|null $version
     * @return ServiceInterface[]
     */
    public function createServicesFromJsonRpc(\Mix\JsonRpc\Server $server, ?string $version = null)
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
            $node = $nodeFactory->createNode($name, sprintf('%s:%d', ServiceHelper::localIP(), $server->port));
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
