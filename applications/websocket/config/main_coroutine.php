<?php

// websocket 应用配置
return [

    // 应用名称
    'appName'    => 'websocket',

    // 应用版本
    'appVersion' => '0.0.0',

    // 应用调试
    'appDebug'   => env('APP_DEBUG'),

    // 基础路径
    'basePath'   => dirname(__DIR__),

    // 组件配置
    'components' => [

        // 错误
        'error'      => [
            // 依赖引用
            'ref' => beanname(Mix\WebSocket\Error::class),
        ],

        // 日志
        'log'        => [
            // 依赖引用
            'ref' => beanname(Mix\Log\Logger::class),
        ],

        // 注册器
        'registry'   => [
            // 依赖引用
            'ref' => beanname(Mix\WebSocket\Registry::class),
        ],

        // 请求
        'request'    => [
            // 依赖引用
            'ref' => beanname(Mix\Http\Message\Request\HttpRequest::class),
        ],

        // 响应
        'response'   => [
            // 依赖引用
            'ref' => beanname(Mix\Http\Message\Response\HttpResponse::class),
        ],

        // WebSocket连接
        'ws'         => [
            // 依赖引用
            'ref' => beanname(Mix\WebSocket\WebSocketConnection::class),
        ],

        // WebSocket帧
        'frame'      => [
            // 依赖引用
            'ref' => beanname(Mix\WebSocket\Frame::class),
        ],

        // Tcp会话
        'tcpSession' => [
            // 依赖引用
            'ref' => beanname(Mix\Tcp\Session\TcpSession::class),
        ],

        // Auth
        'auth'       => [
            // 依赖引用
            'ref' => beanname(Mix\Auth\Authorization::class),
        ],

        // Session
        'session'    => [
            // 依赖引用
            'ref' => beanname(Mix\Http\Session\HttpSession::class),
        ],

        // 连接池
        'dbPool'     => [
            // 依赖引用
            'ref' => beanname(Mix\Database\Pool\ConnectionPool::class),
        ],

        // 连接池
        'redisPool'  => [
            // 依赖引用
            'ref' => beanname(Mix\Redis\Pool\ConnectionPool::class),
        ],

    ],

    // 依赖配置
    'beans'      => [

        // 错误
        [
            // 类路径
            'class'      => Mix\WebSocket\Error::class,
            // 属性
            'properties' => [
                // 错误级别
                'level' => E_ALL,
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

        // 注册器
        [
            // 类路径
            'class'      => Mix\WebSocket\Registry::class,
            // 属性
            'properties' => [
                // 处理器命名空间
                'handlerNamespace'     => 'WebSocket\Handlers',
                // 拦截器命名空间
                'interceptorNamespace' => 'WebSocket\Interceptors',
                // 注册规则
                'rules'                => [
                    '/websocket' => ['WebSocketHandler', 'interceptor' => 'WebSocketInterceptor'],
                ],
            ],
        ],

        // 请求
        [
            // 类路径
            'class' => Mix\Http\Message\Request\HttpRequest::class,
        ],

        // 响应
        [
            // 类路径
            'class' => Mix\Http\Message\Response\HttpResponse::class,
        ],

        // WebSocket连接
        [
            // 类路径
            'class' => Mix\WebSocket\WebSocketConnection::class,
        ],

        // WebSocket帧
        [
            // 类路径
            'class' => Mix\WebSocket\Frame::class,
        ],

        // Tcp会话
        [
            // 类路径
            'class'      => Mix\Tcp\Session\TcpSession::class,
            // 属性
            'properties' => [
                // 处理器
                'handler' => [
                    // 依赖引用
                    'ref' => beanname(Mix\Tcp\Session\ArrayHandler::class),
                ],
            ],
        ],

        // Tcp会话处理器
        [
            // 类路径
            'class' => Mix\Tcp\Session\ArrayHandler::class,
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
                // 连接池
                'pool'      => [
                    // 组件引用
                    'component' => 'redisPool',
                ],
                // Key前缀
                'keyPrefix' => 'SESSION:',
            ],
        ],

        // 连接池
        [
            // 类路径
            'class'      => Mix\Database\Pool\ConnectionPool::class,
            // 属性
            'properties' => [
                // 最多可空闲连接数
                'maxIdle'   => 5,
                // 最大连接数
                'maxActive' => 50,
                // 拨号器
                'dialer'    => [
                    // 依赖引用
                    'ref' => beanname(Common\Dialers\DatabaseDialer::class),
                ],
            ],
        ],

        // 连接池拨号
        [
            // 类路径
            'class' => Common\Dialers\DatabaseDialer::class,
        ],

        // 连接池
        [
            // 类路径
            'class'      => Mix\Redis\Pool\ConnectionPool::class,
            // 属性
            'properties' => [
                // 最多可空闲连接数
                'maxIdle'   => 5,
                // 最大连接数
                'maxActive' => 50,
                // 拨号器
                'dialer'    => [
                    // 依赖引用
                    'ref' => beanname(Common\Dialers\RedisDialer::class),
                ],
            ],
        ],

        // 连接池拨号
        [
            // 类路径
            'class' => Common\Dialers\RedisDialer::class,
        ],

        // 数据库
        [
            // 类路径
            'class'      => Mix\Database\Coroutine\PDOConnection::class,
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
            ],
        ],

        // redis
        [
            // 类路径
            'class'      => Mix\Redis\Coroutine\RedisConnection::class,
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
            ],
        ],

    ],

];
