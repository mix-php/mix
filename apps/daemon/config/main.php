<?php

use mix\base\Env;

// Console应用配置
return [

    // 基础路径
    'basePath'         => dirname(__DIR__) . DIRECTORY_SEPARATOR,

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
            // 换行符
            'newline'     => PHP_EOL,
        ],

        // 数据库
        'rdb'    => [
            // 类路径
            'class'     => 'mix\client\PDOPersistent',
            // 数据源格式
            'dsn'       => Env::get('RDB_DNS'),
            // 数据库用户名
            'username'  => Env::get('RDB_USERNAME'),
            // 数据库密码
            'password'  => Env::get('RDB_PASSWORD'),
            // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
            'attribute' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'redis'  => [
            // 类路径
            'class'    => 'mix\client\RedisPersistent',
            // 主机
            'host'     => Env::get('REDIS_HOST'),
            // 端口
            'port'     => Env::get('REDIS_PORT'),
            // 数据库
            'database' => Env::get('REDIS_DATABASE'),
            // 密码
            'password' => Env::get('REDIS_PASSWORD'),
        ],

    ],

    // 对象配置
    'objects'          => [

    ],

];
