<?php

// Console应用配置
return [

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'apps\crontab\commands',

    // 命令
    'commands'         => [

        'assemblyline exec' => ['AssemblyLine', 'Exec'],
        'push exec'         => ['Push', 'Exec'],
        'clear exec'        => ['Clear', 'Exec'],
        'coroutine start'   => ['Coroutine', 'Exec'],

    ],

    // 组件配置
    'components'       => [

        // 输入
        'input'                              => [
            // 类路径
            'class' => 'mix\console\Input',
        ],

        // 输出
        'output'                             => [
            // 类路径
            'class' => 'mix\console\Output',
        ],

        // 错误
        'error'                              => [
            // 类路径
            'class' => 'mix\console\Error',
            // 错误级别
            'level' => E_ALL,
        ],

        // 日志
        'log'                                => [
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

        // 连接池
        'config1.coroutine.pdo.connectionPool'   => [
            // 类路径
            'class'       => 'mix\pool\ConnectionPool',
            // 最小连接数
            'min'         => 5,
            // 最大连接数
            'max'         => 500,
            // 生存时间
            'maxLifetime' => 3600,
        ],

        // 连接池
        'config1.coroutine.redis.connectionPool' => [
            // 类路径
            'class'       => 'mix\pool\ConnectionPool',
            // 最小连接数
            'min'         => 5,
            // 最大连接数
            'max'         => 500,
            // 生存时间
            'maxLifetime' => 3600,
        ],

    ],

    // 类库配置
    'libraries'        => [

        // 数据库
        'config1.coroutine.pdo'    => [
            // 类路径
            'class'          => 'mix\client\PDOCoroutine',
            // 数据源格式
            'dsn'            => env('DB_DSN'),
            // 数据库用户名
            'username'       => env('DB_USERNAME'),
            // 数据库密码
            'password'       => env('DB_PASSWORD'),
            // 连接池
            'connectionPool' => [
                // 组件路径
                'component' => 'config1.coroutine.pdo.connectionPool',
            ],
        ],

        // redis
        'config1.coroutine.redis'  => [
            // 类路径
            'class'          => 'mix\client\RedisCoroutine',
            // 主机
            'host'           => env('REDIS_HOST'),
            // 端口
            'port'           => env('REDIS_PORT'),
            // 数据库
            'database'       => env('REDIS_DATABASE'),
            // 密码
            'password'       => env('REDIS_PASSWORD'),
            // 连接池
            'connectionPool' => [
                // 组件路径
                'component' => 'config1.coroutine.redis.connectionPool',
            ],
        ],

        // 数据库
        'config1.persistent.pdo'   => [
            // 类路径
            'class'     => 'mix\client\PDOPersistent',
            // 数据源格式
            'dsn'       => env('DB_DSN'),
            // 数据库用户名
            'username'  => env('DB_USERNAME'),
            // 数据库密码
            'password'  => env('DB_PASSWORD'),
            // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
            'attribute' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'config1.persistent.redis' => [
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

    ],

];
