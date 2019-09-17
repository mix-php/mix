<?php

namespace App\WebSocket\Commands;

use Mix\Console\CommandLine\Flag;
use Mix\Helper\ProcessHelper;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Log\Logger;
use Mix\Http\Server\HttpServer;
use Mix\WebSocket\Upgrader;

/**
 * Class StartCommand
 * @package App\Tcp\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class StartCommand
{

    /**
     * @var HttpServer
     */
    public $server;

    /**
     * @var Logger
     */
    public $log;

    /**
     * @var callable[]
     */
    public $patterns = [
        '/websocket' => \App\WebSocket\Handlers\WebSocketHandler::class,
    ];

    /**
     * @var Upgrader
     */
    public $upgrader;

    /**
     * StartCommand constructor.
     */
    public function __construct()
    {
        $this->log      = context()->get('log');
        $this->server   = context()->get('httpServer');
        $this->upgrader = new Upgrader();
    }

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
        // 参数重写
        $host = Flag::string(['h', 'host'], '');
        if ($host) {
            $this->server->host = $host;
        }
        $port = Flag::string(['p', 'port'], '');
        if ($port) {
            $this->server->port = $port;
        }
        // 捕获信号
        ProcessHelper::signal([SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->log->info('received signal [{signal}]', ['signal' => $signal]);
            $this->log->info('server shutdown');
            $this->server->shutdown();
            $this->upgrader->destroy();
            ProcessHelper::signal([SIGINT, SIGTERM, SIGQUIT], null);
        });
        // 启动服务器
        $this->start();
    }

    /**
     * 启动服务器
     */
    public function start()
    {
        $server = $this->server;
        $server->handle('/', function (ServerRequest $request, Response $response) {
            $this->handle($request, $response);
        });
        $this->welcome();
        $this->log->info('server start');
        $server->start();
    }

    /**
     * 请求处理
     * @param ServerRequest $request
     * @param Response $response
     */
    public function handle(ServerRequest $request, Response $response)
    {
        $pathinfo = $request->getServerParams()['path_info'] ?: '/';
        if (!isset($this->patterns[$pathinfo])) {
            $response
                ->withBody((new StreamFactory())->createStream('404 Not Found'))
                ->withStatus(404)
                ->end();
            return;
        }
        try {
            $conn = $this->upgrader->Upgrade($request, $response);
        } catch (\Throwable $e) {
            $response
                ->withBody((new StreamFactory())->createStream('401 Unauthorized'))
                ->withStatus(401)
                ->end();
            return;
        }
        $class    = $this->patterns[$pathinfo];
        $callback = new $class($conn);
        call_user_func($callback);
    }

    /**
     * 欢迎信息
     */
    protected function welcome()
    {
        $phpVersion    = PHP_VERSION;
        $swooleVersion = swoole_version();
        $host          = $this->server->host;
        $port          = $this->server->port;
        echo <<<EOL
                              ____
 ______ ___ _____ ___   _____  / /_ _____
  / __ `__ \/ /\ \/ /__ / __ \/ __ \/ __ \
 / / / / / / / /\ \/ _ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\  / .___/_/ /_/ .___/
                     /_/         /_/


EOL;
        println('Server         Name:      mix-websocketd');
        println('System         Name:      ' . strtolower(PHP_OS));
        println("PHP            Version:   {$phpVersion}");
        println("Swoole         Version:   {$swooleVersion}");
        println('Framework      Version:   ' . \Mix::$version);
        println("Listen         Addr:      {$host}");
        println("Listen         Port:      {$port}");
    }

}
