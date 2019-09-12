<?php

// 应用清单
return [

    // 应用名称
    'appName'    => 'mix-app',

    // 应用版本
    'appVersion' => '0.0.0',

    // 应用调试
    'appDebug'   => getenv('APP_DEBUG'),

    // 基础路径
    'basePath'   => dirname(__DIR__),

    // 协程配置
    'coroutine'  => [
        true,
        [
            'max_coroutine' => 300000,
            'hook_flags'    => SWOOLE_HOOK_ALL,
        ],
    ],

    // 命令
    'commands'   => [

        /** Console */
        'he'         => [
            \App\Console\Commands\HelloCommand::class,
            'description' => "\tEcho demo",
            'options'     => [
                [['n', 'name'], 'description' => 'Your name'],
                ['say', 'description' => "\tSay ..."],
            ],
        ],
        'co'         => [
            \App\Console\Commands\CoroutineCommand::class,
            'description' => "\tCoroutine demo",
        ],
        'wg'         => [
            \App\Console\Commands\WaitGroupCommand::class,
            'description' => "\tWaitGroup demo",
        ],
        'cp'         => [
            \App\Console\Commands\CoroutinePoolCommand::class,
            'description' => "\tCoroutine pool demo",
        ],
        'cpd'        => [
            \App\Console\Commands\CoroutinePoolDaemonCommand::class,
            'description' => "\tCoroutine pool daemon demo",
            'options'     => [
                [['d', 'daemon'], 'description' => 'Run in the background'],
            ],
        ],
        'ti'         => [
            \App\Console\Commands\TimerCommand::class,
            'description' => "\tTimer demo",
        ],

        /** Http */
        'http:start' => [
            \App\Http\Commands\StartCommand::class,
            'description' => "Start service",
            'options'     => [
                [['d', 'daemon'], 'description' => "Run in the background"],
                [['p', 'port'], 'description' => "Listen to the specified port"],
            ],
        ],

        /** WebSocket */
        'ws:start'   => [
            \App\WebSocket\Commands\StartCommand::class,
            'description' => "Start service",
            'options'     => [
                [['d', 'daemon'], 'description' => "Run in the background"],
                [['p', 'port'], 'description' => "Listen to the specified port"],
            ],
        ],

        /** Tcp */
        'tcp:start'  => [
            \App\Tcp\Commands\StartCommand::class,
            'description' => "Start service",
            'options'     => [
                [['d', 'daemon'], 'description' => "Run in the background"],
                [['p', 'port'], 'description' => "Listen to the specified port"],
            ],
        ],

        /** Udp */
        'udp:start'  => [
            \App\Udp\Commands\StartCommand::class,
            'description' => "Start service",
            'options'     => [
                [['d', 'daemon'], 'description' => "Run in the background"],
                [['p', 'port'], 'description' => "Listen to the specified port"],
            ],
        ],

    ],

    // 依赖配置
    'beans'      => [

        // 错误
        [
            // 名称
            'name'            => 'error',
            // 作用域
            'scope'           => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'           => \Mix\Console\Error::class,
            // 构造函数注入
            'constructorArgs' => [
                // 错误级别
                E_ALL,
                // 日志
                ['ref' => 'log'],
            ],
        ],

        // 日志
        [
            // 名称
            'name'       => 'log',
            // 作用域
            'scope'      => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'      => \Mix\Log\Logger::class,
            // 属性注入
            'properties' => [
                // 日志记录级别
                'levels'  => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
                // 处理器
                'handler' => ['ref' => \Mix\Log\MultiHandler::class],
            ],
        ],

        // 日志处理器
        [
            // 类路径
            'class'           => \Mix\Log\MultiHandler::class,
            // 构造函数注入
            'constructorArgs' => [
                // 标准输出处理器
                ['ref' => \Mix\Log\StdoutHandler::class],
                // 文件处理器
                ['ref' => \Mix\Log\FileHandler::class],
            ],
        ],

        // 日志标准输出处理器
        [
            // 类路径
            'class' => \Mix\Log\StdoutHandler::class,
        ],

        // 日志文件处理器
        [
            // 类路径
            'class'      => \Mix\Log\FileHandler::class,
            // 属性注入
            'properties' => [
                // 日志目录
                'dir'         => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'logs',
                // 日志轮转类型
                'rotate'      => \Mix\Log\FileHandler::ROTATE_DAY,
                // 最大文件尺寸
                'maxFileSize' => 0,
            ],
        ],

        // 事件调度器
        [
            // 作用域
            'scope'           => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'           => \Mix\Event\EventDispatcher::class,
            // 构造函数注入
            'constructorArgs' => [
                \App\Common\Listeners\DatabaseListener::class,
                \App\Common\Listeners\RedisListener::class,
            ],
        ],

        // Database连接池
        [
            // 名称
            'name'       => 'dbPool',
            // 作用域
            'scope'      => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'      => \Mix\Database\Pool\ConnectionPool::class,
            // 属性注入
            'properties' => [
                // 最多可空闲连接数
                'maxIdle'   => 5,
                // 最大连接数
                'maxActive' => 50,
                // 拨号器
                'dialer'    => ['ref' => \App\Common\Dialers\DatabaseDialer::class],
            ],
        ],

        // Database连接池拨号
        [
            // 类路径
            'class' => \App\Common\Dialers\DatabaseDialer::class,
        ],

        // Redis连接池
        [
            // 名称
            'name'       => 'redisPool',
            // 作用域
            'scope'      => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'      => \Mix\Redis\Pool\ConnectionPool::class,
            // 属性注入
            'properties' => [
                // 最多可空闲连接数
                'maxIdle'   => 5,
                // 最大连接数
                'maxActive' => 50,
                // 拨号器
                'dialer'    => ['ref' => \App\Common\Dialers\RedisDialer::class],
            ],
        ],

        // Redis连接池拨号
        [
            // 类路径
            'class' => \App\Common\Dialers\RedisDialer::class,
        ],

        // Database
        [
            // 类路径
            'class'      => \Mix\Database\Coroutine\Connection::class,
            // 初始化方法
            'initMethod' => 'connect',
            // 属性注入
            'properties' => [
                // 数据源格式
                'dsn'             => getenv('DATABASE_DSN'),
                // 数据库用户名
                'username'        => getenv('DATABASE_USERNAME'),
                // 数据库密码
                'password'        => getenv('DATABASE_PASSWORD'),
                // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
                'attributes'      => [
                    // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    // 超时
                    \PDO::ATTR_TIMEOUT            => 5,
                ],
                // 事件调度器
                'eventDispatcher' => ['ref' => \Mix\Event\EventDispatcher::class],
            ],
        ],

        // Redis
        [
            // 类路径
            'class'      => \Mix\Redis\Coroutine\Connection::class,
            // 初始化方法
            'initMethod' => 'connect',
            // 属性注入
            'properties' => [
                // 主机
                'host'            => getenv('REDIS_HOST'),
                // 端口
                'port'            => getenv('REDIS_PORT'),
                // 数据库
                'database'        => getenv('REDIS_DATABASE'),
                // 密码
                'password'        => getenv('REDIS_PASSWORD'),
                // 超时
                'timeout'         => 5,
                // 事件调度器
                'eventDispatcher' => ['ref' => \Mix\Event\EventDispatcher::class],
            ],
        ],

        // 缓存
        [
            // 名称
            'name'       => 'cache',
            // 作用域
            'scope'      => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'      => \Mix\Cache\Cache::class,
            // 属性注入
            'properties' => [
                // 处理器
                'handler' => ['ref' => \Mix\Cache\FileHandler::class],
            ],
        ],

        // 缓存文件处理器
        [
            // 类路径
            'class'      => \Mix\Cache\FileHandler::class,
            // 属性注入
            'properties' => [
                // 缓存目录
                'dir'        => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'cache',
                // 分区
                'partitions' => 64,
            ],
        ],

        // 缓存Redis处理器
        [
            // 类路径
            'class'      => \Mix\Cache\RedisHandler::class,
            // 属性注入
            'properties' => [
                // 连接池
                'pool'      => ['ref' => 'redisPool'],
                // Key前缀
                'keyPrefix' => 'CACHE:',
            ],
        ],

        // Http服务器
        [
            // 名称
            'name'            => 'httpServer',
            // 类路径
            'class'           => \Mix\Http\Server\HttpServer::class,
            // 构造函数注入
            'constructorArgs' => [
                // host
                '127.0.0.1',
                // port
                9501,
                // ssl
                false,
            ],
        ],

        // 路由
        [
            // 名称
            'name'       => 'route',
            // 类路径
            'class'      => \Mix\Route\Router::class,
            // 初始化方法
            'initMethod' => 'parse',
            // 属性注入
            'properties' => [
                // 默认变量规则
                'defaultPattern' => '[\w-]+',
                // 路由变量规则
                'patterns'       => [
                    'id' => '\d+',
                ],
                // 全局中间件
                'middleware'     => [\App\Http\Middleware\GlobalMiddleware::class],
                // 路由规则
                'rules'          => [
                    // 普通路由
                    '/'                 => [[\App\Http\Controllers\IndexController::class, 'index'], 'middleware' => [\App\Http\Middleware\ActionMiddleware::class]],
                    '/profile/{id}'     => [[\App\Http\Controllers\ProfileController::class, 'index'], 'middleware' => [\App\Http\Middleware\ActionMiddleware::class]],
                    'POST /file/upload' => [[\App\Http\Controllers\FileController::class, 'upload'], 'middleware' => [\App\Http\Middleware\ActionMiddleware::class]],
                    // 分组路由
                    '/v2'               => [
                        // 分组中间件
                        'middleware' => [\App\Http\Middleware\GroupMiddleware::class],
                        // 分组路由规则
                        'rules'      => [
                            // 分组路由
                            'POST /user/create' => [[\App\Http\Controllers\UserController::class, 'create'], 'middleware' => [\App\Http\Middleware\ActionMiddleware::class]],
                        ],
                    ],
                ],
            ],
        ],

        // 授权
        [
            // 名称
            'name'            => 'auth',
            // 作用域
            'scope'           => \Mix\Bean\BeanDefinition::SINGLETON,
            // 类路径
            'class'           => \Mix\Auth\Authorization::class,
            // 构造函数注入
            'constructorArgs' => [
                // Jwt
                ['ref' => \Mix\Auth\Jwt::class],
            ],
        ],

        // Jwt
        [
            // 类路径
            'class'      => \Mix\Auth\Jwt::class,
            // 属性注入
            'properties' => [
                // 钥匙
                'key'       => 'example_key',
                // 签名算法
                'algorithm' => \Mix\Auth\Jwt::ALGORITHM_RS256,
            ],
        ],

        // Session
        [
            // 名称
            'name'       => 'session',
            // 类路径
            'class'      => \Mix\Session\Session::class,
            // 初始化方法
            'initMethod' => 'start',
            // 属性注入
            'properties' => [
                // 处理器
                'handler'        => ['ref' => \Mix\Session\RedisHandler::class],
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

        // Session Redis处理器
        [
            // 类路径
            'class'      => \Mix\Session\RedisHandler::class,
            // 属性注入
            'properties' => [
                // 连接池
                'pool'      => ['ref' => 'redisPool'],
                // Key前缀
                'keyPrefix' => 'SESSION:',
            ],
        ],

        // Tcp服务器
        [
            // 名称
            'name'            => 'tcpServer',
            // 类路径
            'class'           => \Mix\Server\Server::class,
            // 构造函数注入
            'constructorArgs' => [
                // host
                '127.0.0.1',
                // port
                9502,
                // ssl
                false,
            ],
        ],

        // Udp服务器
        [
            // 名称
            'name'            => 'udpServer',
            // 类路径
            'class'           => \Mix\Udp\Server\UdpServer::class,
            // 构造函数注入
            'constructorArgs' => [
                // host
                '127.0.0.1',
                // port
                9503,
            ],
        ],

    ],

];
