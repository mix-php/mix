<?php

namespace App\Udp\Commands;

use Mix\Console\CommandLine\Flag;
use Mix\Helper\ProcessHelper;
use Mix\Log\Logger;
use Mix\Udp\Server\UdpServer;

/**
 * Class StartCommand
 * @package App\Tcp\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class StartCommand
{

    /**
     * @var UdpServer
     */
    public $server;

    /**
     * @var Logger
     */
    public $log;

    /**
     * StartCommand constructor.
     */
    public function __construct()
    {
        $this->log    = context()->get('log');
        $this->server = context()->get('udpServer');
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
        $server->handle(function (\Swoole\Coroutine\Socket $socket, string $data, array $peer) {
            $this->handle($socket, $data, $peer);
        });
        $this->welcome();
        $this->log->info('server start');
        $server->start();
    }

    /**
     * 消息处理
     * @param \Swoole\Coroutine\Socket $socket
     * @param string $data
     * @param array $peer
     * @throws \Throwable
     */
    public function handle(\Swoole\Coroutine\Socket $socket, string $data, array $peer)
    {
        // 回复消息
        $socket->sendTo($peer['address'], $peer['port'], "Receive successful!\n");
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
        println('Server         Name:      mix-udpd');
        println('System         Name:      ' . strtolower(PHP_OS));
        println("PHP            Version:   {$phpVersion}");
        println("Swoole         Version:   {$swooleVersion}");
        println('Framework      Version:   ' . \Mix::$version);
        println("Listen         Addr:      {$host}");
        println("Listen         Port:      {$port}");
    }

}
