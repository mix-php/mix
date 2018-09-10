<?php

// Console应用配置
return [

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'apps\httpd\commands',

    // 命令
    'commands'         => [

        'service start'   => ['Service', 'Start', 'description' => 'Start the mix-httpd service.'],
        'service stop'    => ['Service', 'Stop', 'description' => 'Stop the mix-httpd service.'],
        'service restart' => ['Service', 'Restart', 'description' => 'Restart the mix-httpd service.'],
        'service reload'  => ['Service', 'Reload', 'description' => 'Reload the worker process of the mix-httpd service.'],
        'service status'  => ['Service', 'Status', 'description' => 'Check the status of the mix-httpd service.'],

    ],

    // 组件配置
    'components'       => [

        // 输入
        'input'  => [
            // 类路径
            'class' => 'mix\console\Input',
        ],

        // 输出
        'output' => [
            // 类路径
            'class' => 'mix\console\Output',
        ],

        // 错误
        'error'  => [
            // 类路径
            'class' => 'mix\console\Error',
            // 错误级别
            'level' => E_ALL,
        ],

        // 日志
        'log'    => [
            // 类路径
            'class'       => 'mix\base\Log',
            // 日志记录级别
            'level'       => ['error', 'info', 'debug'],
            // 日志目录
            'logDir'      => 'logs',
            // 日志轮转类型
            'logRotate'   => mix\base\Log::ROTATE_DAY,
            // 最大文件尺寸
            'maxFileSize' => 0,
        ],

    ],

    // 类库配置
    'libraries'        => [

        // HttpServer
        'httpServer' => [

            // 类路径
            'class'       => 'mix\http\HttpServer',

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
                // 连接处理线程数
                'reactor_num'      => 8,
                // 工作进程数
                'worker_num'       => 8,
                // PID 文件
                'pid_file'         => '/var/run/mix-httpd.pid',
                // 日志文件路径
                'log_file'         => '/tmp/mix-httpd.log',
                // 进程的最大任务数
                'max_request'      => 10000,
                // 退出等待时间
                'max_wait_time'    => 60,
                // 异步安全重启
                'reload_async'     => true,
                // 子进程运行用户
                /* 'user'        => 'www', */
            ],

        ],

    ],

];
