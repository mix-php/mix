<?php

// APP配置文件
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DIRECTORY_SEPARATOR,

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
            'class'        => 'mixhttpd\server\HttpServer',
            // 主机
            'host'         => 'localhost',
            // 端口
            'port'         => 9501,

            // 运行时的各项参数：https://wiki.swoole.com/wiki/page/274.html
            'setting'      => [
                'worker_num' => 1,
                'daemonize'  => false,
                'log_file'   => __DIR__ . '/../runtime/log/mixhttpd.log',
            ],

            // 虚拟主机：运行在Server内的Web应用
            'virtualHosts' => [
                // 默认主机
                '*'                        => __DIR__ . '/../../../application/index/config/main_web.php',
                // 匹配单个主机
                'www.t.com'                => __DIR__ . '/../../../application/index/config/main_web.php',
                // 匹配多个主机
                'www.v.com:9501|www.v.com' => __DIR__ . '/../../../application/index/config/main_web.php',
            ],

        ],

    ],

];
