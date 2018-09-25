<?php

// Console应用配置
return [

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'apps\websocketd\commands',

    // 命令
    'commands'         => [

        'service start'   => ['Service', 'Start'],
        'service stop'    => ['Service', 'Stop'],
        'service restart' => ['Service', 'Restart'],
        'service status'  => ['Service', 'Status'],

    ],

    // 组件配置
    'components'       => [

        // 输入
        'input'                    => [
            // 类路径
            'class' => 'mix\console\Input',
        ],

        // 输出
        'output'                   => [
            // 类路径
            'class' => 'mix\console\Output',
        ],

        // 错误
        'error'                    => [
            // 类路径
            'class' => 'mix\console\Error',
            // 错误级别
            'level' => E_ALL,
        ],

        // 日志
        'log'                      => [
            // 类路径
            'class'       => 'mix\base\Log',
            // 日志记录级别
            'level'       => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
            // 日志目录
            'dir'         => 'logs',
            // 日志轮转类型
            'rotate'      => mix\base\Log::ROTATE_DAY,
            // 最大文件尺寸
            'maxFileSize' => 0,
        ],

        // 数据库
        'pdo'                      => [
            // 类路径
            'class'     => 'mix\client\PDOPersistent',
            // 数据源格式
            'dsn'       => env('DB.DSN'),
            // 数据库用户名
            'username'  => env('DB.USERNAME'),
            // 数据库密码
            'password'  => env('DB.PASSWORD'),
            // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
            'attribute' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'redis'                    => [
            // 类路径
            'class'    => 'mix\client\RedisPersistent',
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
        'websocket.sessionReader'  => [
            // 类路径
            'class'         => 'mix\websocket\SessionReader',
            // 保存处理者
            'saveHandler'   => [
                // 类路径
                'class'    => 'mix\client\Redis',
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
        'websocket.tokenReader'    => [
            // 类路径
            'class'         => 'mix\websocket\TokenReader',
            // 保存处理者
            'saveHandler'   => [
                // 类路径
                'class'    => 'mix\client\Redis',
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
        'websocket.messageHandler' => [
            // 类路径
            'class'               => 'mix\websocket\MessageHandler',
            // 控制器命名空间
            'controllerNamespace' => 'apps\websocketd\controllers',
            // 路由规则
            'rules'               => [

                'joinRoom'    => ['Join', 'Room'],
                'messageEmit' => ['Message', 'Emit'],

            ],
        ],

    ],

    // 类库配置
    'libraries'        => [

        // WebSocketServer
        'webSocketServer' => [

            // 类路径
            'class'    => 'mix\websocket\WebSocketServer',
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
        'async.redis'     => [
            // 类路径
            'class'    => 'mix\client\RedisAsync',
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
