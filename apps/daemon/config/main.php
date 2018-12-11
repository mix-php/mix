<?php

// Console应用配置
return [

    // 应用名称
    'appName'          => 'mix-daemon',

    // 应用版本
    'appVersion'       => '0.0.0',

    // 初始化回调
    'initialize'       => [],

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'Apps\Daemon\Commands',

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

    ],

    // 组件配置
    'components'       => [

        // 输入
        'input'     => [
            // 类路径
            'class' => 'Mix\Console\Input',
        ],

        // 输出
        'output'    => [
            // 类路径
            'class' => 'Mix\Console\Output',
        ],

        // 错误
        'error'     => [
            // 类路径
            'class' => 'Mix\Console\Error',
            // 错误级别
            'level' => E_ALL,
        ],

        // 日志
        'log'       => [
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
        'pdo'       => [
            // 类路径
            'class'         => 'Mix\Database\Persistent\PDOConnection',
            // 数据源格式
            'dsn'           => env('DATABASE.DSN'),
            // 数据库用户名
            'username'      => env('DATABASE.USERNAME'),
            // 数据库密码
            'password'      => env('DATABASE.PASSWORD'),
            // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
            'driverOptions' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'redis'     => [
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

        // 连接池
        'pdoPool'   => [
            // 类路径
            'class' => 'Mix\Pool\ConnectionPool',
            // 最小连接数
            'min'   => 5,
            // 最大连接数
            'max'   => 50,
        ],

        // 连接池
        'redisPool' => [
            // 类路径
            'class' => 'Mix\Pool\ConnectionPool',
            // 最小连接数
            'min'   => 5,
            // 最大连接数
            'max'   => 50,
        ],

    ],

    // 类库配置
    'libraries'        => [

        // 数据库
        [
            // 类路径
            'class'         => 'Mix\Database\Coroutine\PDOConnection',
            // 名称
            'name'          => 'default',
            // 数据源格式
            'dsn'           => env('DATABASE.DSN'),
            // 数据库用户名
            'username'      => env('DATABASE.USERNAME'),
            // 数据库密码
            'password'      => env('DATABASE.PASSWORD'),
            // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
            'driverOptions' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        [
            // 类路径
            'class'    => 'Mix\Redis\Coroutine\RedisConnection',
            // 名称
            'name'     => 'default',
            // 主机
            'host'     => env('REDIS.HOST'),
            // 端口
            'port'     => env('REDIS.PORT'),
            // 数据库
            'database' => env('REDIS.DATABASE'),
            // 密码
            'password' => env('REDIS.PASSWORD'),
        ],

        // 数据库
        [
            // 类路径
            'class'         => 'Mix\Database\Persistent\PDOConnection',
            // 名称
            'name'          => 'default',
            // 数据源格式
            'dsn'           => env('DATABASE.DSN'),
            // 数据库用户名
            'username'      => env('DATABASE.USERNAME'),
            // 数据库密码
            'password'      => env('DATABASE.PASSWORD'),
            // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
            'driverOptions' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        [
            // 类路径
            'class'    => 'Mix\Redis\Persistent\RedisConnection',
            // 名称
            'name'     => 'default',
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
