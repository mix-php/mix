<?php

namespace Tcp\Commands;

use Mix\Console\CommandLine\Flag;
use Mix\Helper\ProcessHelper;
use Mix\Log\Logger;
use Mix\Server\Connection;
use Mix\Server\Server;
use Tcp\Helpers\SendHelper;

/**
 * Class StartCommand
 * @package Tcp\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class StartCommand
{

    /**
     * @var Server
     */
    public $server;

    /**
     * @var Logger
     */
    public $log;

    /**
     * @var callable[]
     */
    public $methods = [
        'hello.world' => [\Tcp\Controllers\HelloController::class, 'world'],
    ];

    /**
     * EOF
     */
    const EOF = "\r\n";

    /**
     * StartCommand constructor.
     */
    public function __construct()
    {
        $this->log    = context()->get('log');
        $this->server = context()->get('tcpServer');
        $this->init();
    }

    /**
     * 初始化
     */
    public function init()
    {
        // 实例化控制器
        foreach ($this->methods as $method => $callback) {
            list($class, $action) = $callback;
            $this->methods[$method] = [new $class, $action];
        }
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
        // 捕获信号
        ProcessHelper::signal([SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->log->info('received signal [{signal}]', ['signal' => $signal]);
            $this->log->info('server shutdown');
            $this->server->shutdown();
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
        $server->handle(function (Connection $conn) {
            $this->handle($conn);
        });
        $server->set([
            'open_eof_check' => true,
            'package_eof'    => static::EOF,
        ]);
        $this->welcome();
        $this->log->info('server start');
        $server->start();
    }

    /**
     * 连接处理
     * @param Connection $conn
     * @throws \Throwable
     */
    public function handle(Connection $conn)
    {
        while (true) {
            try {
                $data = $conn->recv();
            } catch (\Throwable $e) {
                // 忽略服务器主动关闭连接抛出的104错误
                if ($e->getCode() == 104) {
                    return;
                }
                throw $e;
            }
            xgo([$this, 'runAction'], $conn, $data);
        }
    }

    /**
     * 执行功能
     * @param Connection $conn
     * @param $data
     */
    public function runAction(Connection $conn, $data)
    {
        // 解析数据
        $data = json_decode($data, true);
        if (!$data) {
            SendHelper::error($conn, -32600, 'Invalid Request');
            return;
        }
        if (!isset($data['method']) || !isset($data['params']) || !isset($data['id'])) {
            SendHelper::error($conn, -32700, 'Parse error');
            return;
        }
        // 定义变量
        $method = $data['method'];
        $params = $data['params'];
        $id     = $data['id'];
        // 路由到控制器
        if (!isset($this->methods[$method])) {
            SendHelper::error($conn, -32601, 'Method not found', $id);
            return;
        }
        // 执行
        $result = call_user_func($this->methods[$method], $params);
        SendHelper::data($conn, $result, $id);
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
        println('Server         Name:      mix-tcpd');
        println('System         Name:      ' . strtolower(PHP_OS));
        println("PHP            Version:   {$phpVersion}");
        println("Swoole         Version:   {$swooleVersion}");
        println('Framework      Version:   ' . \Mix::$version);
        println("Listen         Addr:      {$host}");
        println("Listen         Port:      {$port}");
    }

}
