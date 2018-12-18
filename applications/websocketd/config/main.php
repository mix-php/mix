<?php

// Console应用配置
return [

    // 应用名称
    'appName'          => 'mix-websocketd',

    // 应用版本
    'appVersion'       => '1.0.0',

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'WebSocketd\Commands',

    // 命令
    'commands'         => [

        'service start'   => ['Service/Start', 'description' => ''],
        'service stop'    => ['Service/Stop', 'description' => ''],
        'service restart' => ['Service/Restart', 'description' => ''],
        'service status'  => ['Service/Status', 'description' => ''],

    ],

    // 组件配置
    'components'       => [

        // 错误
        'error'          => [
            // 类路径
            'class' => 'Mix\Console\Error',
            // 错误级别
            'level' => E_ALL,
        ],

        // 日志
        'log'            => [
            // 类路径
            'class'   => 'Mix\Log\Logger',
            // 日志记录级别
            'levels'  => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
            // 处理者
            'handler' => [
                // 类路径
                'class'       => 'Mix\Log\FileHandler',
                // 日志目录
                'dir'         => 'logs',
                // 日志轮转类型
                'rotate'      => Mix\Log\FileHandler::ROTATE_DAY,
                // 最大文件尺寸
                'maxFileSize' => 0,
            ],
        ],

        // 数据库
        'pdo'            => [
            // 类路径
            'class'     => 'Mix\Database\Persistent\PDOConnection',
            // 数据源格式
            'dsn'       => env('DATABASE.DSN'),
            // 数据库用户名
            'username'  => env('DATABASE.USERNAME'),
            // 数据库密码
            'password'  => env('DATABASE.PASSWORD'),
            // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
            'attribute' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'redis'          => [
            // 类路径
            'class'    => 'Mix\Redis\Persistent\RedisConnection',
            // 主机
            'host'     => env('REDIS.HOST'),
            // 端口
            'port'     => env('REDIS.PORT'),
            // 数据库
            'database' => env('REDIS.DATABASE'),
            // 密码
            'password' => env('REDIS.PASSWORD'),
        ],

        // Session
        'sessionReader'  => [
            // 类路径
            'class'         => 'Mix\WebSocket\SessionReader',
            // 保存处理者
            'saveHandler'   => [
                // 类路径
                'class'    => 'Mix\Redis\RedisConnection',
                // 主机
                'host'     => env('REDIS.HOST'),
                // 端口
                'port'     => env('REDIS.PORT'),
                // 数据库
                'database' => env('REDIS.DATABASE'),
                // 密码
                'password' => env('REDIS.PASSWORD'),
            ],
            // 保存的Key前缀
            'saveKeyPrefix' => 'SESSION:',
            // session名
            'name'          => 'session_id',
        ],

        // Token
        'tokenReader'    => [
            // 类路径
            'class'         => 'Mix\WebSocket\TokenReader',
            // 保存处理者
            'saveHandler'   => [
                // 类路径
                'class'    => 'Mix\Redis\RedisConnection',
                // 主机
                'host'     => env('REDIS.HOST'),
                // 端口
                'port'     => env('REDIS.PORT'),
                // 数据库
                'database' => env('REDIS.DATABASE'),
                // 密码
                'password' => env('REDIS.PASSWORD'),
            ],
            // 保存的Key前缀
            'saveKeyPrefix' => 'TOKEN:',
            // token键名
            'name'          => 'access_token',
        ],

        // 消息处理器
        'messageHandler' => [
            // 类路径
            'class'               => 'Mix\WebSocket\MessageHandler',
            // 控制器命名空间
            'controllerNamespace' => 'WebSocketd\Controllers',
            // 路由规则
            'rules'               => [

                'joinRoom'    => ['Join', 'Room'],
                'messageEmit' => ['Message', 'Emit'],

            ],
        ],

    ],

    // 类库配置
    'libraries'        => [

        // 服务器
        [
            // 类路径
            'class'    => 'Mix\WebSocket\Server',
            // 主机
            'host'     => 'localhost',
            // 端口
            'port'     => 9502,
            // 运行时的各项参数：https://wiki.swoole.com/wiki/page/274.html
            'settings' => [
                // 开启协程
                'enable_coroutine' => false,
                // 连接处理线程数
                'reactor_num'      => 8,
                // 工作进程数
                'worker_num'       => 8,
                // 数据包分发策略
                'dispatch_mode'    => 2,
                // PID 文件
                'pid_file'         => '/var/run/mix-websocketd.pid',
                // 日志文件路径
                'log_file'         => '/tmp/mix-websocketd.log',
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

        // 异步redis
        [
            // 类路径
            'class'    => 'Mix\Redis\Async\RedisConnection',
            // 主机
            'host'     => env('REDIS.HOST'),
            // 端口
            'port'     => env('REDIS.PORT'),
            // 数据库
            'database' => env('REDIS.DATABASE'),
            // 密码
            'password' => env('REDIS.PASSWORD'),
        ],

    ],

];
