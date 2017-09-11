<?php

// APP配置文件
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DS,

    // 控制器命名空间
    'controllerNamespace' => 'mixhttpd\command',

    // 注册树
    'register'            => [

        // 路由
        'route'    => [
            // 类路径
            'class'          => 'mix\base\Route',
            // 默认变量规则
            'defaultPattern' => '[\w-]+',
            // 路由变量规则
            'patterns'       => [
                'id' => '\d+',
            ],
            // 路由规则
            'rules'          => [

            ],
        ],

        // 请求
        'request'  => [
            // 类路径
            'class' => 'mix\console\Request',
        ],

        // 响应
        'response' => [
            // 类路径
            'class' => 'mix\console\Response',
        ],

        // HttpServer
        'server'   => [
            // 类路径
            'class'        => 'mix\swoole\HttpServer',
            // 主机
            'host'         => '192.168.181.130',
            // 端口
            'port'         => 8501,
            // 运行时的各项参数：https://wiki.swoole.com/wiki/page/274.html
            'setting'      => [
                'worker_num' => 4,
                'daemonize'  => true,
                'log_file'   => __DIR__ . '/../runtime/log/mixhttpd.log',
            ],
            // 虚拟主机：运行在Server内的Web应用
            'virtualHosts' => [

                '*' => [
                    // APP路径
                    'class'  => 'mix\swoole\Application',
                    // 配置路径
                    'config' => __DIR__ . '/../../application/index/config/main_swoole.php',
                ],

            ],
        ],

    ],

];
