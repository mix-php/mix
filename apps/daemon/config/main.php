<?php

// Console应用配置
$database = require __DIR__ . '/../../common/config/database.php';
return [

    // 基础路径
    'basePath'         => dirname(__DIR__) . DIRECTORY_SEPARATOR,

    // 命令命名空间
    'commandNamespace' => 'apps\daemon\commands',

    // 命令
    'commands'         => [

        'multi start'   => ['Multi', 'Start'],
        'multi stop'    => ['Multi', 'Stop'],
        'multi restart' => ['Multi', 'Restart'],
        'multi status'  => ['Multi', 'Status'],

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
        'rdb'    => array_merge(
            $database['mysql'],
            [
                // 类路径
                'class'          => 'mix\client\PdoPersistent',
                // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
                'attribute'      => [
                    // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ],
                // 连接持续时间(秒)
                'persistentTime' => 7200,
            ]
        ),

        // redis
        'redis'  => array_merge(
            $database['redis'],
            [
                // 类路径
                'class'          => 'mix\client\RedisPersistent',
                // 连接持续时间(秒)
                'persistentTime' => 7200,
            ]
        ),

    ],

    // 对象配置
    'objects'          => [

    ],

];
