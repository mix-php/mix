<?php

// Console应用配置
return [

    // 应用名称
    'appName'          => 'mix-httpd',

    // 应用版本
    'appVersion'       => '1.0.0',

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'Httpd\Commands',

    // 命令
    'commands'         => [

        'service start'   => ['Service\Start', 'description' => 'Start the mix-httpd service.'],
        'service stop'    => ['Service\Stop', 'description' => 'Stop the mix-httpd service.'],
        'service restart' => ['Service\Restart', 'description' => 'Restart the mix-httpd service.'],
        'service reload'  => ['Service\Reload', 'description' => 'Reload the worker process of the mix-httpd service.'],
        'service status'  => ['Service\Status', 'description' => 'Check the status of the mix-httpd service.'],

    ],

    // 组件配置
    'components'       => [

        // 错误
        'error' => [
            // 依赖引用
            'ref' => beanname(Mix\Console\Error::class),
        ],

        // 日志
        'log'   => [
            // 依赖引用
            'ref' => beanname(Mix\Log\Logger::class),
        ],

    ],

    // 依赖配置
    'beans'            => [

        // 错误
        [
            // 类路径
            'class'      => Mix\Console\Error::class,
            // 属性
            'properties' => [
                // 错误级别
                'level' => E_ALL,
            ],
        ],

        // 日志
        [
            // 类路径
            'class'      => Mix\Log\Logger::class,
            // 属性
            'properties' => [
                // 日志记录级别
                'levels'  => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
                // 处理者
                'handler' => [
                    // 依赖引用
                    'ref' => beanname(Mix\Log\FileHandler::class),
                ],
            ],
        ],

        // 处理者
        [
            // 类路径
            'class'      => Mix\Log\FileHandler::class,
            // 属性
            'properties' => [
                // 日志目录
                'dir'         => 'logs',
                // 日志轮转类型
                'rotate'      => Mix\Log\FileHandler::ROTATE_DAY,
                // 最大文件尺寸
                'maxFileSize' => 0,
            ],
        ],

        // 服务器
        [
            // 类路径
            'class'      => Mix\Http\Server::class,
            // 属性
            'properties' => [
                // 虚拟主机：运行在服务器内的 HTTP 服务
                'virtualHost' => [
                    // 主机
                    'host'       => '127.0.0.1',
                    // 端口
                    'port'       => 9501,
                    // 配置文件
                    'configFile' => __DIR__ . '/http_permanent.php',
                ],
                // 运行参数：https://wiki.swoole.com/wiki/page/274.html
                'settings'    => [
                    // 开启协程
                    'enable_coroutine' => false,
                    // 主进程事件处理线程数
                    'reactor_num'      => 8,
                    // 工作进程数
                    'worker_num'       => 8,
                    // 进程的最大任务数
                    'max_request'      => 10000,
                    // PID 文件
                    'pid_file'         => '/var/run/mix-httpd.pid',
                    // 日志文件路径
                    'log_file'         => '/tmp/mix-httpd.log',
                    // 子进程运行用户
                    /* 'user'        => 'www', */
                ],
            ],
        ],

    ],

];
