<?php

// Apache/PHP-FPM 传统环境下运行的 Web 应用配置
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DIRECTORY_SEPARATOR,

    // 控制器命名空间
    'controllerNamespace' => 'apps\index\controllers',

    // 中间件命名空间
    'middlewareNamespace' => 'apps\index\middleware',

    // 全局中间件
    'middleware'          => [],

    // 组件配置
    'components'          => [

        // 路由
        'route'    => [
            // 类路径
            'class'          => 'mix\http\Route',
            // 默认变量规则
            'defaultPattern' => '[\w-]+',
            // 路由变量规则
            'patterns'       => [
                'id' => '\d+',
            ],
            // 路由规则
            'rules'          => [
                // 一级路由
                ':controller/:action' => [':controller', ':action', 'middleware' => ['Before']],
            ],
            // URL后缀
            'suffix'         => '.html',
        ],

        // 请求
        'request'  => [
            // 类路径
            'class' => 'mix\http\compatible\Request',
        ],

        // 响应
        'response' => [
            // 类路径
            'class'         => 'mix\http\compatible\Response',
            // 默认输出格式
            'defaultFormat' => mix\http\Response::FORMAT_HTML,
            // json
            'json'          => [
                // 类路径
                'class' => 'mix\http\Json',
            ],
            // jsonp
            'jsonp'         => [
                // 类路径
                'class' => 'mix\http\Jsonp',
                // callback键名
                'name'  => 'callback',
            ],
            // xml
            'xml'           => [
                // 类路径
                'class' => 'mix\http\Xml',
            ],
        ],

        // 错误
        'error'    => [
            // 类路径
            'class'  => 'mix\http\Error',
            // 输出格式
            'format' => mix\http\Error::FORMAT_HTML,
            // 错误级别
            'level'  => E_ALL,
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
            'maxFileSize' => 0,
            // 换行符
            'newline'     => PHP_EOL,
            // 在写入时加独占锁
            'writeLock'   => false,
        ],

        // Token
        'token'    => [
            // 类路径
            'class'         => 'mix\http\Token',
            // 保存处理者
            'saveHandler'   => [
                // 类路径
                'class'    => 'mix\client\Redis',
                // 主机
                'host'     => env('REDIS_HOST'),
                // 端口
                'port'     => env('REDIS_PORT'),
                // 数据库
                'database' => env('REDIS_DATABASE'),
                // 密码
                'password' => env('REDIS_PASSWORD'),
            ],
            // 保存的Key前缀
            'saveKeyPrefix' => 'MIXTKID:',
            // 有效期
            'expires'       => 604800,
            // token键名
            'name'          => 'access_token',
        ],

        // Session
        'session'  => [
            // 类路径
            'class'         => 'mix\http\Session',
            // 保存处理者
            'saveHandler'   => [
                // 类路径
                'class'    => 'mix\client\Redis',
                // 主机
                'host'     => env('REDIS_HOST'),
                // 端口
                'port'     => env('REDIS_PORT'),
                // 数据库
                'database' => env('REDIS_DATABASE'),
                // 密码
                'password' => env('REDIS_PASSWORD'),
            ],
            // 保存的Key前缀
            'saveKeyPrefix' => 'MIXSSID:',
            // 生存时间
            'expires'       => 7200,
            // session键名
            'name'          => 'mixssid',
        ],

        // Cookie
        'cookie'   => [
            // 类路径
            'class'    => 'mix\http\Cookie',
            // 过期时间
            'expire'   => 31536000,
            // 有效的服务器路径
            'path'     => '/',
            // 有效域名/子域名
            'domain'   => '',
            // 仅通过安全的 HTTPS 连接传给客户端
            'secure'   => false,
            // 仅可通过 HTTP 协议访问
            'httponly' => false,
        ],

        // 数据库
        'rdb'      => [
            // 类路径
            'class'     => 'mix\client\PDO',
            // 数据源格式
            'dsn'       => env('RDB_DNS'),
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
        'redis'    => [
            // 类路径
            'class'    => 'mix\client\Redis',
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

    // 对象配置
    'objects'             => [

    ],

];
