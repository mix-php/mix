<?php

// APP配置文件
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DS,

    // 控制器命名空间
    'controllerNamespace' => 'www\command',

    // 注册树
    'register'            => [

        // 配置
        'config'   => [
            // 类路径
            'class'    => 'express\base\Config',
            // 自动加载
            'autoload' => ['common', 'local'],
        ],

        // 路由
        'route'    => [
            // 类路径
            'class'          => 'express\base\Route',
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
            'class' => 'express\console\Request',
        ],

        // 响应
        'response' => [
            // 类路径
            'class'  => 'express\console\Response',
        ],

        // rdb
        'rdb'      => [
            // 类路径
            'class'                => 'express\rdb\Pdo',
            // 数据源格式
            'dsn'                  => 'mysql:host=127.0.0.1;port=3306;charset=utf8;dbname=test',
            // 数据库用户名
            'username'             => 'root',
            // 数据库密码
            'password'             => '123456',
            // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
            'attribute'            => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                // 转换 NULL 和空字符串（在所有驱动中都可用，不仅限于Oracle）
                \PDO::ATTR_ORACLE_NULLS       => \PDO::NULL_NATURAL,
                // 强制列名为指定的大小写
                \PDO::ATTR_CASE               => \PDO::CASE_NATURAL,
            ],
            // 回滚含有零影响行数的事务
            'rollbackZeroAffected' => false,
        ],

        // redis
        'redis'    => [
            // 类路径
            'class'    => 'express\nosql\Redis',
            // 主机
            'host'     => '127.0.0.1',
            // 端口
            'port'     => 6379,
            // 密码
            'password' => '',
            // 数据库
            'database' => 0,
        ],

    ],

];
