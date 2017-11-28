<?php

// Console应用配置
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DIRECTORY_SEPARATOR,

    // 控制器命名空间
    'controllerNamespace' => 'index\command',

    // 组件配置
    'components'          => [

        // 路由
        'route'    => [
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
        'request'  => [
            // 类路径
            'class' => 'mix\console\Request',
        ],

        // 响应
        'response' => [
            // 类路径
            'class' => 'mix\console\Response',
        ],

        // 错误
        'error'    => [
            // 类路径
            'class' => 'mix\console\Error',
        ],

        // 日志
        'log'      => [
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
        ],

        // 数据库
        'rdb'      => [
            // 类路径
            'class'                           => 'mix\rdb\Pdo',
            // 数据源格式
            'dsn'                             => 'mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=test',
            // 数据库用户名
            'username'                        => 'root',
            // 数据库密码
            'password'                        => '',
            // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
            'attribute'                       => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'redis'    => [
            // 类路径
            'class'    => 'mix\nosql\Redis',
            // 主机
            'host'     => '127.0.0.1',
            // 端口
            'port'     => 6379,
            // 数据库
            'database' => 0,
            // 密码
            'password' => '',
        ],

        // 配置
        'config'   => [
            // 类路径
            'class'    => 'mix\base\Config',
            // 自动加载
            'autoload' => ['common', 'local'],
        ],

    ],

    // 对象配置
    'objects'              => [

    ],

];
