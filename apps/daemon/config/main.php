<?php

// Console应用配置
return [

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'apps\daemon\commands',

    // 命令
    'commands'         => [

        'assemblyline start'   => ['AssemblyLine', 'Start'],
        'assemblyline stop'    => ['AssemblyLine', 'Stop'],
        'assemblyline restart' => ['AssemblyLine', 'Restart'],
        'assemblyline status'  => ['AssemblyLine', 'Status'],

        'push start'   => ['Push', 'Start'],
        'push stop'    => ['Push', 'Stop'],
        'push restart' => ['Push', 'Restart'],
        'push status'  => ['Push', 'Status'],

        'single start'   => ['Single', 'Start'],
        'single stop'    => ['Single', 'Stop'],
        'single restart' => ['Single', 'Restart'],
        'single status'  => ['Single', 'Status'],

        'coroutine start' => ['Coroutine', 'Start'],

    ],

    // 组件配置
    'components'       => [

        // 输入
        'input'              => [
            // 类路径
            'class' => 'mix\console\Input',
        ],

        // 输出
        'output'             => [
            // 类路径
            'class' => 'mix\console\Output',
        ],

        // 错误
        'error'              => [
            // 类路径
            'class' => 'mix\console\Error',
            // 错误级别
            'level' => E_ALL,
        ],

        // 日志
        'log'                => [
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

        // 数据库
        'rdb'                => [
            // 类路径
            'class'     => 'mix\client\PDOPersistent',
            // 数据源格式
            'dsn'       => env('RDB_DSN'),
            // 数据库用户名
            'username'  => env('RDB_USERNAME'),
            // 数据库密码
            'password'  => env('RDB_PASSWORD'),
            // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
            'attribute' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'redis'              => [
            // 类路径
            'class'    => 'mix\client\RedisPersistent',
            // 主机
            'host'     => env('REDIS_HOST'),
            // 端口
            'port'     => env('REDIS_PORT'),
            // 数据库
            'database' => env('REDIS_DATABASE'),
            // 密码
            'password' => env('REDIS_PASSWORD'),
        ],

        // 连接池
        'pdo.connectionPool' => [
            // 类路径
            'class'       => 'mix\coroutine\ConnectionPool',
            // 最小连接数
            'min'         => 5,
            // 最大连接数
            'max'         => 2000,
            // 生存时间
            'maxLifetime' => 59,
        ],

    ],

    // 类库配置
    'libraries'        => [

        // 数据库
        'pdo' => [
            // 类路径
            'class'          => 'mix\coroutine\PDO',
            // 数据源格式
            'dsn'            => env('RDB_DSN'),
            // 数据库用户名
            'username'       => env('RDB_USERNAME'),
            // 数据库密码
            'password'       => env('RDB_PASSWORD'),
            // 连接池
            'connectionPool' => [
                // 组件路径
                'component' => 'pdo.connectionPool',
            ],
        ],

    ],

];
