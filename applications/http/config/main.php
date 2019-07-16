<?php

// Console应用配置
return [

    // 应用名称
    'appName'    => 'mix-http',

    // 应用版本
    'appVersion' => '0.0.0',

    // 应用调试
    'appDebug'   => getenv('APP_DEBUG'),

    // 基础路径
    'basePath'   => dirname(__DIR__),

    // 命令
    'commands'   => [

        'start' => [
            \Http\Commands\StartCommand::class,
            'description' => "\tStart the mix-httpd service",
            'options'     => [
                [['d', 'daemon'], 'description' => "\t" . 'Run in the background'],
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
                'dir'         => 'logs',
                // 日志轮转类型
                'rotate'      => \Mix\Log\FileHandler::ROTATE_DAY,
                // 最大文件尺寸
                'maxFileSize' => 0,
            ],
        ],

        // 服务器
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
                'middleware'     => [\Http\Middleware\GlobalMiddleware::class],
                // 路由规则
                'rules'          => [
                    // 普通路由
                    '/'             => [[\Http\Controllers\IndexController::class, 'index'], 'middleware' => [\Http\Middleware\ActionMiddleware::class]],
                    '/profile/{id}' => [[\Http\Controllers\ProfileController::class, 'index'], 'middleware' => [\Http\Middleware\ActionMiddleware::class]],
                    '/file/upload'  => [[\Http\Controllers\FileController::class, 'upload'], 'middleware' => [\Http\Middleware\ActionMiddleware::class]],
                    // 分组路由
                    '/v2'           => [
                        // 分组中间件
                        'middleware' => [\Http\Middleware\GroupMiddleware::class],
                        // 分组路由规则
                        'rules'      => [
                            // 分组路由
                            '/user/create' => [[\Http\Controllers\UserController::class, 'create'], 'middleware' => [\Http\Middleware\ActionMiddleware::class]],
                        ],
                    ],
                ],
            ],
        ],

    ],

];
