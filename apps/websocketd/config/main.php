<?php

// Console应用配置
$database = require __DIR__ . '/../../common/config/database.php';
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DIRECTORY_SEPARATOR,

    // 控制器命名空间
    'controllerNamespace' => 'apps\websocketd\commands',

    // 组件配置
    'components'          => [

        // 路由
        'route'                    => [
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
        'request'                  => [
            // 类路径
            'class' => 'mix\console\Request',
        ],

        // 响应
        'response'                 => [
            // 类路径
            'class' => 'mix\console\Response',
        ],

        // 错误
        'error'                    => [
            // 类路径
            'class' => 'mix\console\Error',
        ],

        // 日志
        'log'                      => [
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
            // 换行符
            'newline'     => PHP_EOL,
        ],

        // 数据库
        'rdb'                      => array_merge(
            $database['mysql'],
            [
                // 类路径
                'class'     => 'mix\client\PdoPersistent',
                // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
                'attribute' => [
                    // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ],
            ]
        ),

        // redis
        'redis'                    => array_merge(
            $database['redis'],
            [
                // 类路径
                'class' => 'mix\client\RedisPersistent',
            ]
        ),

        // Session
        'webSocket.sessionReader'  => [
            // 类路径
            'class'         => 'mix\websocket\SessionReader',
            // 保存处理者
            'saveHandler'   => array_merge(
                $database['redis'],
                [
                    // 类路径
                    'class' => 'mix\client\Redis',
                ]
            ),
            // 保存的Key前缀
            'saveKeyPrefix' => 'MIXSSID:',
            // session名
            'name'          => 'mixssid',
        ],

        // Token
        'webSocket.tokenReader'    => [
            // 类路径
            'class'         => 'mix\websocket\TokenReader',
            // 保存处理者
            'saveHandler'   => array_merge(
                $database['redis'],
                [
                    // 类路径
                    'class' => 'mix\client\Redis',
                ]
            ),
            // 保存的Key前缀
            'saveKeyPrefix' => 'MIXTKID:',
            // token键名
            'name'          => 'access_token',
        ],

        // 消息处理器
        'webSocket.messageHandler' => [
            // 类路径
            'class'               => 'mix\websocket\MessageHandler',
            // 控制器命名空间
            'controllerNamespace' => 'apps\websocketd\controllers',
            // 路由规则
            'rules'               => [

                'join'    => 'join/room',
                'message' => 'message/to',

            ],
        ],

    ],

    // 对象配置
    'objects'             => [

        // WebSocketServer
        'webSocketServer' => [

            // 类路径
            'class'   => 'mix\server\WebSocketServer',
            // 主机
            'host'    => 'localhost',
            // 端口
            'port'    => 9502,

            // 运行时的各项参数：https://wiki.swoole.com/wiki/page/274.html
            'setting' => [
                // 连接处理线程数
                'reactor_num' => 8,
                // 工作进程数
                'worker_num'  => 8,
                // 设置worker进程的最大任务数
                'max_request' => 10000,
                // 日志文件路径
                'log_file'    => '/tmp/mix-websocketd.log',
                // 子进程运行用户
                /* 'user'        => 'www', */
            ],

        ],

        // 异步redis
        'asyncRedis'      => array_merge(
            $database['redis'],
            [
                // 类路径
                'class' => 'mix\async\Redis',
            ]
        ),

    ],

];
