<?php

namespace Mix\Grpc;

use Mix\Grpc\Exception\NotFoundException;
use Mix\Grpc\Exception\RuntimeException;
use Mix\Grpc\Helper\GrpcHelper;

/**
 * Class Server
 * @package Mix\Grpc
 */
class Server
{

    /**
     * @var callable[]
     */
    protected $callables = [];

    /**
     * @param string[]|object[] $objectsOrClasses
     */
    public function register(...$objectsOrClasses): void
    {
        $register = function ($objectOrClass) {
            if (is_object($objectOrClass)) {
                $class = get_class($objectOrClass);
            } else {
                $class = $objectOrClass;
            }
            if (!is_subclass_of($class, ServiceInterface::class)) {
                throw new \InvalidArgumentException(sprintf('%s is not a subclass of %s', $class, ServiceInterface::class));
            }
            $name = $class::NAME;
            if (!$name) {
                throw new \InvalidArgumentException(sprintf('Const %s::NAME can\'t be empty', $class));
            }

            $methods = get_class_methods($class);
            $reflectClass = new \ReflectionClass($class);
            foreach ($methods as $method) {
                if (strpos($method, '_') === 0) {
                    continue;
                }

                $reflectMethod = $reflectClass->getMethod($method);
                if ($reflectMethod->getNumberOfParameters() != 2) {
                    throw new \InvalidArgumentException(sprintf('%s::%s wrong number of parameters', $class, $method));
                }

                $this->callables[sprintf('/%s/%s', $name, $method)] = [$objectOrClass, $method];
            }
        };

        foreach ($objectsOrClasses as $objectOrClass) {
            $register($objectOrClass);
        }
    }

    /**
     * @param \Closure|null $init
     * @return \Closure
     */
    public function handler(?\Closure $init = null): \Closure
    {
        return function (...$args) use ($init) {
            static $ok = false;
            if (!$ok && $init) {
                $init();
                $ok = true;
            }

            if (static::isSwoole($args)) {
                /**
                 * @var $request \Swoole\Http\Request
                 * @var $response \Swoole\Http\Response
                 */
                list($request, $response) = $args;
                $ctx = Context::fromSwoole($request, $response);
                $this->dispatch($request->server['request_method'], $request->server['path_info'] ?: '/', $ctx);
            } else {
                throw new RuntimeException('The current usage scenario is not supported');
            }
        };
    }

    /**
     * @param string $method
     * @param string $uri
     * @param Context $ctx
     * @throws NotFoundException
     */
    protected function dispatch(string $method, string $uri, Context $ctx)
    {
        try {
            if (!in_array($ctx->request->header['content-type'] ?? '', [
                'application/grpc',
                'application/grpc+proto',
            ])) {
                throw new RuntimeException('Invalid the Content-Type', 500);
            }
            if ($method != 'POST') {
                throw new NotFoundException('', 405);
            }
            if (!isset($this->callables[$uri])) {
                throw new NotFoundException('', 404);
            }

            // 序列化
            list($objectOrClass, $method) = $this->callables[$uri];
            $reflectClass = new \ReflectionClass($objectOrClass);
            $reflectMethod = $reflectClass->getMethod($method);
            $reflectParameter = $reflectMethod->getParameters()[1];
            $rpcRequestClass = $reflectParameter->getType()->getName();
            $rpcRequest = new $rpcRequestClass;
            GrpcHelper::deserialize($rpcRequest, $ctx->request->getContent());

            // 执行
            if (!is_object($objectOrClass)) {
                $object = new $objectOrClass;
            } else {
                $object = $objectOrClass;
            }
            $parameters = [];
            array_push($parameters, $ctx);
            array_push($parameters, $rpcRequest);
            $rpcResponse = call_user_func_array([$object, $method], $parameters);
        } catch (NotFoundException $ex) {
            $status = $ex->getCode();
            $ex = null;
        } catch (\Throwable $ex) {
            $status = 500;
        } finally {
            $ctx->response->header('content-type', 'application/grpc');
            if (isset($rpcResponse)) {
                $content = GrpcHelper::serialize($rpcResponse);
            }
            $ctx->response->status($status ?? 200);
            static::trailerHandle($ctx->response, $ex ?? null);
            $ctx->response->end($content ?? '');
        }
    }

    /**
     * @param array $args
     * @return bool
     */
    protected static function isSwoole(array $args): bool
    {
        if (count($args) != 2) {
            return false;
        }
        list($request, $response) = $args;
        if ($request instanceof \Swoole\Http\Request && $response instanceof \Swoole\Http\Response) {
            return true;
        }
        return false;
    }

    /**
     * @param \Swoole\Http\Response $response
     * @param \Throwable|null $ex
     */
    protected static function trailerHandle(\Swoole\Http\Response $response, ?\Throwable $ex): void
    {
        $response->header('trailer', 'grpc-status, grpc-message');
        if (!is_null($ex)) {
            $response->trailer('grpc-status', $ex->getCode() == 0 ? -1 : $ex->getCode());
            $response->trailer('grpc-message', $ex->getMessage());
        } else {
            $response->trailer('grpc-status', 0);
            $response->trailer('grpc-message', '');
        }
    }

}
