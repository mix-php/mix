<?php

// APP配置文件
return [

    // 基础路径
    'basePath'            => dirname(__DIR__) . DS,

    // 控制器命名空间
    'controllerNamespace' => 'www\controller',

    // 注册树
    'register'            => [

        // 配置
        'config'   => [
            // 类路径
            'class'    => 'express\base\Config',
            // 自动加载
            'autoload' => ['common'],
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
            'class' => 'express\web\Request',
        ],

        // 响应
        'response' => [
            // 类路径
            'class'  => 'express\web\Response',
            // 输出格式
            'format' => express\web\Response::FORMAT_JSON,
            // json
            'json'   => [
                // 类路径
                'class' => 'express\web\Json',
            ],
            // jsonp
            'jsonp'  => [
                // 类路径
                'class'        => 'express\web\Jsonp',
                // callback名称
                'callbackName' => 'callback',
            ],
            // xml
            'xml'    => [
                // 类路径
                'class' => 'express\web\Xml',
            ],
        ],

        // 错误
        'error'    => [
            // 类路径
            'class'  => 'express\web\Error',
            // 输出格式
            'format' => express\web\Error::FORMAT_HTML,
        ],

        // Session
        'session'  => [
            // 类路径
            'class'         => 'express\web\Session',
            // 处理者
            'saveHandler'   => express\web\Session::HANDLER_REDIS,
            // 保存路径
            'savePath'      => 'tcp://127.0.0.1:6379?auth=&database=9&prefix=EXPRESS_SSID:',
            // 生存时间
            'gcMaxLifetime' => 7200,
            // session名
            'name'          => 'EXPRESS_SSID',
        ],

        // Cookie
        'cookie'   => [
            // 类路径
            'class'    => 'express\web\Cookie',
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

    ],

];
