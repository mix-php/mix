<?php

// Apache/PHP-FPM 传统环境下运行的 HTTP 服务配置（传统模式）
return [

    // 应用名称
    'appName'    => 'http',

    // 应用版本
    'appVersion' => '0.0.0',

    // 应用调试
    'appDebug'   => env('APP_DEBUG'),

    // 基础路径
    'basePath'   => dirname(__DIR__),

    // 组件配置
    'components' => [

        // 错误
        'error'    => [
            // 依赖引用
            'ref' => beanname(Mix\Http\Error::class),
        ],

        // 日志
        'log'      => [
            // 依赖引用
            'ref' => beanname(Mix\Log\Logger::class),
        ],

        // 路由
        'route'    => [
            // 依赖引用
            'ref' => beanname(Mix\Http\Route::class),
        ],

        // 请求
        'request'  => [
            // 依赖引用
            'ref' => beanname(Mix\Http\Message\Request\Compatible\HttpRequest::class),
        ],

        // 响应
        'response' => [
            // 依赖引用
            'ref' => beanname(Mix\Http\Message\Response\Compatible\HttpResponse::class),
        ],

        // Auth
        'auth'     => [
            // 依赖引用
            'ref' => beanname(Mix\Auth\Authorization::class),
        ],

        // Session
        'session'  => [
            // 依赖引用
            'ref' => beanname(Mix\Http\Session\HttpSession::class),
        ],

        // 数据库
        'db'       => [
            // 依赖引用
            'ref' => beanname(Mix\Database\PDOConnection::class),
        ],

        // redis
        'redis'    => [
            // 依赖引用
            'ref' => beanname(Mix\Redis\RedisConnection::class),
        ],

        // 文件缓存
        'cache'    => [
            // 依赖引用
            'ref' => beanname(Mix\Cache\Cache::class),
        ],

    ],

    // 依赖配置
    'beans'      => [

        // 错误
        [
            // 类路径
            'class'      => Mix\Http\Error::class,
            // 属性
            'properties' => [
                // 输出格式
                'format' => Mix\Http\Error::FORMAT_HTML,
                // 错误级别
                'level'  => E_ALL,
            ],
        ],

        // 日志
        [
            // 类路径
            'class'      => Mix\Log\Logger::class,
            // 属性
            'properties' => [
                // 日志记录级别
                'levels'  => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
                // 处理器
                'handler' => [
                    // 依赖引用
                    'ref' => beanname(Mix\Log\MultiHandler::class),
                ],
            ],
        ],

        // 日志处理器
        [
            // 类路径
            'class'      => Mix\Log\MultiHandler::class,
            // 属性
            'properties' => [
                // 日志处理器集合
                'handlers' => [
                    // 标准输出处理器
                    [
                        // 依赖引用
                        'ref' => beanname(Mix\Log\StdoutHandler::class),
                    ],
                    // 文件处理器
                    [
                        // 依赖引用
                        'ref' => beanname(Mix\Log\FileHandler::class),
                    ],
                ],
            ],
        ],

        // 日志标准输出处理器
        [
            // 类路径
            'class' => Mix\Log\StdoutHandler::class,
        ],

        // 日志文件处理器
        [
            // 类路径
            'class'      => Mix\Log\FileHandler::class,
            // 属性
            'properties' => [
                // 日志目录
                'dir'         => 'logs',
                // 日志轮转类型
                'rotate'      => Mix\Log\FileHandler::ROTATE_DAY,
                // 最大文件尺寸
                'maxFileSize' => 0,
            ],
        ],

        // 路由
        [
            // 类路径
            'class'      => Mix\Http\Route::class,
            // 属性
            'properties' => [
                // 控制器命名空间
                'controllerNamespace' => 'Http\Controllers',
                // 中间件命名空间
                'middlewareNamespace' => 'Http\Middleware',
                // 默认变量规则
                'defaultPattern'      => '[\w-]+',
                // 路由变量规则
                'patterns'            => [
                    'id' => '\d+',
                ],
                // 全局中间件
                'middleware'          => ['After'],
                // 路由规则
                'rules'               => [
                    // 一级路由
                    '/{controller}/{action}' => ['{controller}', '{action}', 'middleware' => ['Before']],
                ],
            ],
        ],

        // 请求
        [
            // 类路径
            'class' => Mix\Http\Message\Request\Compatible\HttpRequest::class,
        ],

        // 响应
        [
            // 类路径
            'class'      => Mix\Http\Message\Response\Compatible\HttpResponse::class,
            // 属性
            'properties' => [
                // 默认输出格式
                'defaultFormat' => Mix\Http\Message\Response\HttpResponseInterface::FORMAT_HTML,
                // json
                'json'          => [
                    // 依赖引用
                    'ref' => beanname(Mix\Http\Message\Json::class),
                ],
                // jsonp
                'jsonp'         => [
                    // 依赖引用
                    'ref' => beanname(Mix\Http\Message\Jsonp::class),
                ],
                // xml
                'xml'           => [
                    // 依赖引用
                    'ref' => beanname(Mix\Http\Message\Xml::class),
                ],
            ],
        ],

        // json
        [
            // 类路径
            'class' => Mix\Http\Message\Json::class,
        ],

        // jsonp
        [
            // 类路径
            'class'      => Mix\Http\Message\Jsonp::class,
            // 属性
            'properties' => [
                // callback键名
                'name' => 'callback',
            ],
        ],

        // xml
        [
            // 类路径
            'class' => Mix\Http\Message\Xml::class,
        ],

        // Auth
        [
            // 类路径
            'class'      => Mix\Auth\Authorization::class,
            // 属性
            'properties' => [
                // token提取器
                'tokenExtractor' => [
                    // 依赖引用
                    'ref' => beanname(Mix\Auth\BearerTokenExtractor::class),
                ],
                // jwt
                'jwt'            => [
                    // 依赖引用
                    'ref' => beanname(Mix\Auth\JWT::class),
                ],
            ],
        ],

        // token提取器
        [
            // 类路径
            'class' => Mix\Auth\BearerTokenExtractor::class,
        ],

        // jwt
        [
            // 类路径
            'class'      => Mix\Auth\JWT::class,
            // 属性
            'properties' => [
                // 钥匙
                'key'       => 'example_key',
                // 签名算法
                'algorithm' => Mix\Auth\JWT::ALGORITHM_HS256,
            ],
        ],

        // Session
        [
            // 类路径
            'class'      => Mix\Http\Session\HttpSession::class,
            // 属性
            'properties' => [
                // 处理器
                'handler'        => [
                    // 依赖引用
                    'ref' => beanname(Mix\Http\Session\RedisHandler::class),
                ],
                // session键名
                'name'           => 'session_id',
                // 生存时间
                'maxLifetime'    => 7200,
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
        ],

        // Session处理器
        [
            // 类路径
            'class'      => Mix\Http\Session\RedisHandler::class,
            // 属性
            'properties' => [
                // 连接
                'connection' => [
                    // 组件引用
                    'component' => 'redis',
                ],
                // Key前缀
                'keyPrefix'  => 'SESSION:',
            ],
        ],

        // 数据库
        [
            // 类路径
            'class'      => Mix\Database\PDOConnection::class,
            // 属性
            'properties' => [
                // 数据源格式
                'dsn'           => env('DATABASE_DSN'),
                // 数据库用户名
                'username'      => env('DATABASE_USERNAME'),
                // 数据库密码
                'password'      => env('DATABASE_PASSWORD'),
                // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
                'driverOptions' => [
                    // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ],
                // 监听器
                'listener'      => [
                    // 依赖引用
                    'ref' => beanname(Common\Listeners\DatabaseListener::class),
                ],
            ],
        ],

        // 数据库监听器
        [
            // 类路径
            'class' => Common\Listeners\DatabaseListener::class,
        ],

        // redis
        [
            // 类路径
            'class'      => Mix\Redis\RedisConnection::class,
            // 属性
            'properties' => [
                // 主机
                'host'     => env('REDIS_HOST'),
                // 端口
                'port'     => env('REDIS_PORT'),
                // 数据库
                'database' => env('REDIS_DATABASE'),
                // 密码
                'password' => env('REDIS_PASSWORD'),
                // 监听器
                'listener'      => [
                    // 依赖引用
                    'ref' => beanname(Common\Listeners\RedisListener::class),
                ],
            ],
        ],

        // redis监听器
        [
            // 类路径
            'class' => Common\Listeners\RedisListener::class,
        ],

        // 文件缓存
        [
            // 类路径
            'class'      => Mix\Cache\Cache::class,
            // 属性
            'properties' => [
                // 处理器
                'handler' => [
                    'ref' => beanname(Mix\Cache\FileHandler::class),
                ],
            ],
        ],

        // 文件缓存处理器
        [
            // 类路径
            'class'      => Mix\Cache\FileHandler::class,
            // 属性
            'properties' => [
                // 缓存目录
                'dir'        => 'cache',
                // 分区
                'partitions' => 64,
            ],
        ],

    ],

];
