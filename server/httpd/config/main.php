<?php

// Console应用配置
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DIRECTORY_SEPARATOR,

    // 控制器命名空间
    'controllerNamespace' => 'httpd\command',

    // 组件配置
    'components'            => [

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

        // 错误
        'error'    => [
            // 类路径
            'class' => 'mix\console\Error',
        ],

        // 日志
        'log'      => [
            // 类路径
            'class'       => 'mix\base\Log',
            // 日志记录级别
            'level'       => ['error', 'info', 'debug'],
            // 日志目录
            'logDir'      => 'logs',
            // 日志轮转类型
            'logRotate'   => mix\base\Log::ROTATE_DAY,
            // 最大文件尺寸
            'maxFileSize' => 2048 * 1024,
        ],

        // HttpServer
        'server'   => [

            // 类路径
            'class'        => 'httpd\library\HttpServer',
            // 主机
            'host'         => '127.0.0.1',
            // 端口
            'port'         => 9501,

            // 运行时的各项参数：https://wiki.swoole.com/wiki/page/274.html
            'setting'      => [
                // 连接处理线程数
                'reactor_num' => 8,
                // 工作进程数
                'worker_num'  => 8,
                // 日志文件路径
                'log_file'    => __DIR__ . '/../runtime/logs/mixhttpd.log',
                // 子进程运行用户
                'user'        => 'www',
            ],

            // 虚拟主机：运行在Server内的Web应用
            'virtualHosts' => [
                // 默认主机
                '*' => __DIR__ . '/../../../application/index/config/main_web.php',
            ],

        ],

    ],

];
