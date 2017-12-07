<?php

// Apache/PHP-FPM 传统环境下运行的Web应用配置
$database = require __DIR__ . '/../../common/config/database.php';
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DIRECTORY_SEPARATOR,

    // 控制器命名空间
    'controllerNamespace' => 'app\index\controller',

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
            'class' => 'mix\web\Request',
        ],

        // 响应
        'response' => [
            // 类路径
            'class'         => 'mix\web\Response',
            // 默认输出格式
            'defaultFormat' => mix\swoole\Response::FORMAT_JSON,
            // json
            'json'          => [
                // 类路径
                'class' => 'mix\web\Json',
            ],
            // jsonp
            'jsonp'         => [
                // 类路径
                'class'        => 'mix\web\Jsonp',
                // callback名称
                'callbackName' => 'callback',
            ],
            // xml
            'xml'           => [
                // 类路径
                'class' => 'mix\web\Xml',
            ],
        ],

        // 错误
        'error'    => [
            // 类路径
            'class'  => 'mix\web\Error',
            // 输出格式
            'format' => mix\web\Error::FORMAT_HTML,
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

        // Token
        'token'    => [
            // 类路径
            'class'   => 'mix\web\Token',
            // 处理者
            'handler' => array_merge(
                $database['redis'],
                [
                    // 类路径
                    'class'  => 'mix\client\Redis',
                    // Key前缀
                    'prefix' => 'MIXTKID:',
                ]
            ),
            // 有效期
            'expires' => 604800,
            // session名
            'name'    => 'access_token',
        ],

        // Session
        'session'  => [
            // 类路径
            'class'   => 'mix\web\Session',
            // 处理者
            'handler' => array_merge(
                $database['redis'],
                [
                    // 类路径
                    'class'  => 'mix\client\Redis',
                    // Key前缀
                    'prefix' => 'MIXSSID:',
                ]
            ),
            // 生存时间
            'expires' => 7200,
            // session名
            'name'    => 'MIXSSID',
        ],

        // Cookie
        'cookie'   => [
            // 类路径
            'class'    => 'mix\web\Cookie',
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
        'rdb'      => array_merge(
            $database['mysql'],
            [
                // 类路径
                'class'     => 'mix\client\Pdo',
                // 设置PDO属性: http://php.net/manual/zh/pdo.setattribute.php
                'attribute' => [
                    // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ],
            ]
        ),

        // redis
        'redis'    => array_merge(
            $database['redis'],
            [
                // 类路径
                'class' => 'mix\client\Redis',
            ]
        ),


    ],

    // 对象配置
    'objects'             => [

    ],

];
