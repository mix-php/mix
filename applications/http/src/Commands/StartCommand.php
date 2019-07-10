<?php

namespace Mix\Http\Server\Support\Commands;

use Mix\Console\CommandLine\Flag;
use Mix\Helper\ProcessHelper;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Response;
use Mix\Http\Message\Stream\ContentStream;
use Mix\Http\Server\HttpServer;
use Mix\Http\Server\Middleware\MiddlewareDispatcher;
use Mix\Route\Router;
use Mix\View\View;

/**
 * Class StartCommand
 * @package Mix\Http\Server\Support\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class StartCommand
{

    /**
     * @var HttpServer
     */
    public $http;

    /**
     * 主函数
     */
    public function main()
    {
        // 守护处理
        $daemon = Flag::bool(['d', 'daemon'], false);
        if ($daemon) {
            ProcessHelper::daemon();
        }
        // 捕获信号
        ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->http and $this->http->shutdown();
            ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], null);
        });
        // 启动服务器
        xgo([$this, 'start']);
    }

    /**
     * 启动服务器
     */
    public function start()
    {
        /** @var HttpServer $http $http */
        $http = $this->http = app()->get('httpServer');
        $http->handle('/', function (ServerRequest $request, Response $response) {
            xgo([$this, 'handle'], $request, $response);
        });
        $http->start();
    }

    /**
     * 请求处理
     * @param ServerRequest $request
     * @param Response $response
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function handle(ServerRequest $request, Response $response)
    {
        /** @var Router $route */
        $route = app()->get('route');
        try {
            $matchRule = $route->match($request->getMethod(), $request->getHeaderLine('path_info') ?: '/');
        } catch (\Throwable $e) {
            // 404 处理
            static::show404($e, $response);
            return;
        }
        // 保存路由参数
        foreach ($matchRule->getParams() as $key => $value) {
            $request->withAttribute($key, $value);
        }
        // 执行
        try {
            // 执行中间件
            $dispatcher = new MiddlewareDispatcher($matchRule->getMiddleware(), $request, $response);
            $response   = $dispatcher->dispatch();
            // 执行控制器
            if (!$response->getBody()) {
                $response = call_user_func($matchRule->getCallback(), $request, $response);
            }
            /** @var Response $response */
            $response->send();
        } catch (\Throwable $e) {
            // 500 处理
            static::show500($e, $response);
            return;
        }
    }

    /**
     * 404处理
     * @param \Throwable $e
     * @param Response $response
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public static function show404(\Throwable $e, Response $response)
    {
        $view = new View();
        $html = $view->renderPartial('errors.not_found', [
            'message' => $e->getMessage(),
            'type'    => get_class($e),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
        $response->withBody(new ContentStream($html))
            ->withStatus(404)
            ->withAddedHeader('Content-Type', 'text/html; charset=UTF-8')
            ->send();
    }

    /**
     * 500处理
     * @param \Throwable $e
     * @param Response $response
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public static function show500(\Throwable $e, Response $response)
    {
        $view = new View();
        $html = $view->renderPartial('errors.internal_server_error', [
            'message' => $e->getMessage(),
            'type'    => get_class($e),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
        $response->withBody(new ContentStream($html))
            ->withStatus(500)
            ->withAddedHeader('Content-Type', 'text/html; charset=UTF-8')
            ->send();
    }

}
