<?php

// mix-httpd 下运行的 HTTP 服务配置（常驻协程模式）
return [

    // 基础路径
    'basePath'            => dirname(__DIR__),

    // 控制器命名空间
    'controllerNamespace' => 'Httpd\Controllers',

    // 中间件命名空间
    'middlewareNamespace' => 'Httpd\Middleware',

    // 全局中间件
    'middleware'          => [],

    // 组件配置
    'components'          => [

        // 路由
        'route'     => [
            // 类路径
            'class'          => 'Mix\Http\Route',
            // 默认变量规则
            'defaultPattern' => '[\w-]+',
            // 路由变量规则
            'patterns'       => [
                'id' => '\d+',
            ],
            // 路由规则
            'rules'          => [
                // 一级路由
                '{controller}/{action}' => ['{controller}', '{action}', 'middleware' => ['Before']],
            ],
        ],

        // 请求
        'request'   => [
            // 类路径
            'class' => 'Mix\Http\Request',
        ],

        // 响应
        'response'  => [
            // 类路径
            'class'         => 'Mix\Http\Response',
            // 默认输出格式
            'defaultFormat' => Mix\Http\Response::FORMAT_HTML,
            // json
            'json'          => [
                // 类路径
                'class' => 'Mix\Http\Json',
            ],
            // jsonp
            'jsonp'         => [
                // 类路径
                'class' => 'Mix\Http\Jsonp',
                // callback键名
                'name'  => 'callback',
            ],
            // xml
            'xml'           => [
                // 类路径
                'class' => 'Mix\Http\Xml',
            ],
        ],

        // 错误
        'error'     => [
            // 类路径
            'class'  => 'Mix\Http\Error',
            // 输出格式
            'format' => Mix\Http\Error::FORMAT_HTML,
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

        // Token
        'token'     => [
            // 类路径
            'class'     => 'Mix\Http\Token',
            // 连接池
            'pool'      => [
                // 组件路径
                'component' => 'redisPool',
            ],
            // Key前缀
            'keyPrefix' => 'TOKEN:',
            // 有效期
            'expiresIn' => 604800,
            // token键名
            'name'      => 'access_token',
        ],

        // Session
        'session'   => [
            // 类路径
            'class'          => 'Mix\Http\Session',
            // 连接池
            'pool'           => [
                // 组件路径
                'component' => 'redisPool',
            ],
            // Key前缀
            'keyPrefix'      => 'SESSION:',
            // 生存时间
            'maxLifetime'    => 7200,
            // session键名
            'name'           => 'session_id',
            // 过期时间
            'cookieExpires'  => 0,
            // 有效的服务器路径
            'cookiePath'     => '/',
            // 有效域名/子域名
            'cookieDomain'   => '',
            // 仅通过安全的 HTTPS 连接传给客户端
            'cookieSecure'   => false,
            // 仅可通过 HTTP 协议访问
            'cookieHttpOnly' => false,
        ],

        // 连接池
        'redisPool' => [
            // 类路径
            'class'     => 'Mix\Redis\Coroutine\RedisPool',
            // 最多可空闲连接数
            'maxIdle'   => 5,
            // 最大连接数
            'maxActive' => 50,
            // 主机
            'host'      => env('REDIS.HOST'),
            // 端口
            'port'      => env('REDIS.PORT'),
            // 数据库
            'database'  => env('REDIS.DATABASE'),
            // 密码
            'password'  => env('REDIS.PASSWORD'),
        ],

        // 连接池
        'pdoPool'   => [
            // 类路径
            'class'     => 'Mix\Database\Coroutine\PDOPool',
            // 最多可空闲连接数
            'maxIdle'   => 5,
            // 最大连接数
            'maxActive' => 50,
            // 数据源格式
            'dsn'       => env('DATABASE.DSN'),
            // 数据库用户名
            'username'  => env('DATABASE.USERNAME'),
            // 数据库密码
            'password'  => env('DATABASE.PASSWORD'),
        ],

    ],

    // 类库配置
    'libraries'           => [

    ],

];
