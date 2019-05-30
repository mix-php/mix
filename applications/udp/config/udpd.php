<?php

// udp 服务配置
return [

    // 服务器
    'server'      => [
        // 主机
        'host' => '127.0.0.1',
        // 端口
        'port' => 9504,
    ],

    // 应用
    'application' => [
        // 配置信息
        'config' => require __DIR__ . '/main_coroutine.php',
    ],

    // 运行参数：https://wiki.swoole.com/wiki/page/274.html
    'setting'     => [
        // 开启协程
        'enable_coroutine' => true,
        // 主进程事件处理线程数
        'reactor_num'      => 8,
        // 工作进程数
        'worker_num'       => 8,
        // PID 文件
        'pid_file'         => '/var/run/mix-udpd.pid',
        // 日志文件路径
        'log_file'         => '/tmp/mix-udpd.log',
        // 子进程运行用户
        /* 'user'             => 'www', */
    ],

];
