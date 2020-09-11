<?php

namespace Mix\FastRoute;

use FastRoute\Dispatcher;
use Mix\FastRoute\Helper\ConfigHelper;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareDispatcher;
use Mix\FastRoute\Exception\NotFoundException;
use Mix\Http\Server\ServerHandlerInterface;
use Mix\Micro\Route\RouterInterface;

/**
 * Class Router
 * @package Mix\FastRoute
 */
class Router implements ServerHandlerInterface, RouterInterface
{

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $globalMiddleware;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Router constructor.
     * @param string|callable|null $routeDefinition callback or file path or null
     * @param array $globalMiddleware
     */
    public function __construct($routeDefinition = null, array $globalMiddleware = [])
    {
        $this->options = [
            'routeParser'    => 'FastRoute\\RouteParser\\Std',
            'dataGenerator'  => 'FastRoute\\DataGenerator\\GroupCountBased',
            'dispatcher'     => 'FastRoute\\Dispatcher\\GroupCountBased',
            'routeCollector' => 'Mix\\FastRoute\\RouteCollector',
        ];

        if (is_string($routeDefinition)) {
            $this->load($routeDefinition);
        }
        if ($routeDefinition instanceof \Closure) {
            $this->parse($routeDefinition);
        }

        $this->globalMiddleware = $globalMiddleware;
    }

    /**
     * Parse
     * @param callable $routeDefinitionCallback
     */
    public function parse(callable $routeDefinitionCallback)
    {
        $options = $this->options;
        /** @var RouteCollector $routeCollector */
        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        $routeDefinitionCallback($routeCollector);
        $this->data       = $routeCollector->getData();
        $this->dispatcher = new $options['dispatcher']($this->data);
    }

    /**
     * Load
     * @param string $path file or dir
     * @throws \RuntimeException
     */
    public function load(string $path)
    {
        $callbacks = ConfigHelper::each($path);

        $options = $this->options;
        /** @var RouteCollector $routeCollector */
        $routeCollector = new $options['routeCollector'](
            new $options['routeParser'], new $options['dataGenerator']
        );
        foreach ($callbacks as $callback) {
            if (!($callback instanceof \Closure)) {
                throw new \RuntimeException(sprintf('Invalid route configuration: %s', $path));
            }
            $callback($routeCollector);
        }
        $this->data       = $routeCollector->getData();
        $this->dispatcher = new $options['dispatcher']($this->data);
    }

    /**
     * 获取 url 规则映射的全部 service 名称
     *
     * Url                  Service
     * /                    index
     * /foo                 foo
     * /foo/bar             foo
     * /foo/bar/baz         foo
     * /foo/bar/baz/cat     foo.bar
     * /v1/foo/bar          v1.foo
     * /v1/foo/bar/baz      v1.foo
     * /v1/foo/bar/baz/cat  v1.foo.bar
     *
     * @return string[][] [name => [pattern,...]]
     */
    public function services()
    {
        $patterns = [];
        foreach ($this->data as $datum) {
            foreach ($datum as $method => $item) {
                foreach ($item as $key => $value) {
                    $pattern = $key;
                    if (is_numeric($key)) {
                        $pattern = $value['regex'];
                    }
                    $patterns[] = $pattern;
                }
            }
        }

        $services = [];
        foreach ($patterns as $pattern) {
            $tmp   = str_replace('^/', '*', $pattern);
            $slice = array_filter(explode('/', strtolower($tmp)));
            foreach ($slice as $key => $value) {
                if (!preg_match('/^[a-z0-9]+$/i', $value) && strpos($value, ')') === false) {
                    $slice[$key] = '';
                }
            }
            $slice = array_filter($slice);

            $version = '';
            if (isset($slice[0]) && stripos($slice[0], 'v') === 0) {
                $version = array_shift($slice) . '.';
            }

            switch (count($slice)) {
                case 0:
                    $name = 'index';
                    break;
                case 1:
                case 2:
                case 3:
                    $name = array_shift($slice);
                    break;
                default:
                    array_pop($slice);
                    array_pop($slice);
                    $name = implode('.', $slice);
            }
            $services[$version . $name][] = $pattern;
        }
        return $services;
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param Response $response
     * @throws \Throwable
     */
    public function handleHTTP(ServerRequest $request, Response $response)
    {
        // 支持 micro web 的代理
        // micro web 代理无法将 /foo/ 后面的杠传递过来
        $microBasePath = $request->getHeaderLine('x-micro-web-base-path');
        if ($microBasePath) {
            $uri = $request->getUri();
            $uri->withPath(sprintf('%s%s', $microBasePath, $uri->getPath() == '/' ? '' : $uri->getPath()));
            $serverParams                = $request->getServerParams();
            $serverParams['request_uri'] = sprintf('%s%s', $microBasePath, $serverParams['request_uri'] == '/' ? '' : $serverParams['request_uri']);
            $serverParams['path_info']   = sprintf('%s%s', $microBasePath, $serverParams['path_info'] == '/' ? '' : $serverParams['path_info']);
            $request->withServerParams($serverParams);
        }

        // 调用全局中间件
        try {
            $process    = function (ServerRequest $request, Response $response) {
                return $response;
            };
            $dispatcher = new MiddlewareDispatcher($this->globalMiddleware, $process, $request, $response);
            $response   = $dispatcher->dispatch();
        } catch (\Throwable $ex) {
            // 500 处理
            $this->error500($ex, $response)->send();
            throw $ex;
        }
        if ($response->getBody()) {
            $response->send();
            return;
        }

        // 路由匹配
        try {
            if (!isset($this->dispatcher)) {
                throw new NotFoundException('Not Found (#404)');
            }
            $result = $this->dispatcher->dispatch($request->getMethod(), $request->getServerParams()['path_info'] ?: '/');
            switch ($result[0]) {
                case \FastRoute\Dispatcher::FOUND:
                    list($handler, $middleware) = $result[1];
                    if (!$handler instanceof \Closure && !is_object($handler[0])) {
                        $handler[0] = new $handler[0];
                    }
                    $vars = $result[2];
                    break;
                default:
                    throw new NotFoundException('Not Found (#404)');
            }
        } catch (NotFoundException $ex) {
            // 404 处理
            $this->error404($ex, $response)->send();
            return;
        }
        // 保存路由参数
        foreach ($vars as $key => $value) {
            $request->withAttribute($key, $value);
        }

        // 通过中间件执行
        try {
            $process    = function (ServerRequest $request, Response $response) use ($handler) {
                // 构造方法内的参数是为了方便继承封装使用
                // 为了支持 \Closure 移除了构造方法传参数，为路由支持 websocket
                return call_user_func($handler, $request, $response);
            };
            $dispatcher = new MiddlewareDispatcher($middleware, $process, $request, $response);
            $response   = $dispatcher->dispatch();
        } catch (\Throwable $ex) {
            // 500 处理
            $this->error500($ex, $response)->send();
            throw $ex;
        }

        $response->send();
    }

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return Response
     */
    public function error404(\Throwable $exception, Response $response): Response
    {
        $content = '404 Not Found';
        $body    = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(404);
    }

    /**
     * 500 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return Response
     */
    public function error500(\Throwable $exception, Response $response): Response
    {
        $content = [
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
        ];
        $body    = (new StreamFactory())->createStream(json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withContentType('application/json', 'utf-8')
            ->withBody($body)
            ->withStatus(500);
    }

}
