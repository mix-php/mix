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

    ],

    // 组件配置
    'components'       => [

        // 输入
        'input'                          => [
            // 类路径
            'class' => 'mix\console\Input',
        ],

        // 输出
        'output'                         => [
            // 类路径
            'class' => 'mix\console\Output',
        ],

        // 错误
        'error'                          => [
            // 类路径
            'class' => 'mix\console\Error',
            // 错误级别
            'level' => E_ALL,
        ],

        // 日志
        'log'                            => [
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
        'pdo'                            => [
            // 类路径
            'class'         => 'mix\client\PDOPersistent',
            // 数据源格式
            'dsn'           => env('DB.DSN'),
            // 数据库用户名
            'username'      => env('DB.USERNAME'),
            // 数据库密码
            'password'      => env('DB.PASSWORD'),
            // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
            'driverOptions' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'redis'                          => [
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

        // 连接池
        'coroutine.pdo.connectionPool'   => [
            // 类路径
            'class' => 'mix\pool\ConnectionPool',
            // 最小连接数
            'min'   => 5,
            // 最大连接数
            'max'   => 50,
        ],

        // 连接池
        'coroutine.redis.connectionPool' => [
            // 类路径
            'class' => 'mix\pool\ConnectionPool',
            // 最小连接数
            'min'   => 5,
            // 最大连接数
            'max'   => 50,
        ],

    ],

    // 类库配置
    'libraries'        => [

        // 数据库
        'coroutine.pdo'    => [
            // 类路径
            'class'          => 'mix\client\PDOCoroutine',
            // 数据源格式
            'dsn'            => env('DB.DSN'),
            // 数据库用户名
            'username'       => env('DB.USERNAME'),
            // 数据库密码
            'password'       => env('DB.PASSWORD'),
            // 连接池
            'connectionPool' => [
                // 组件路径
                'component' => 'coroutine.pdo.connectionPool',
            ],
        ],

        // redis
        'coroutine.redis'  => [
            // 类路径
            'class'          => 'mix\client\RedisCoroutine',
            // 主机
            'host'           => env('REDIS.HOST'),
            // 端口
            'port'           => env('REDIS.PORT'),
            // 数据库
            'database'       => env('REDIS.DATABASE'),
            // 密码
            'password'       => env('REDIS.PASSWORD'),
            // 连接池
            'connectionPool' => [
                // 组件路径
                'component' => 'coroutine.redis.connectionPool',
            ],
        ],

        // 数据库
        'persistent.pdo'   => [
            // 类路径
            'class'         => 'mix\client\PDOPersistent',
            // 数据源格式
            'dsn'           => env('DB.DSN'),
            // 数据库用户名
            'username'      => env('DB.USERNAME'),
            // 数据库密码
            'password'      => env('DB.PASSWORD'),
            // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
            'driverOptions' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'persistent.redis' => [
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

    ],

];
