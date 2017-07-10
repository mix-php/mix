<?php

// APP配置文件
return [

    'basePath'            => dirname(__DIR__) . DS,

    // 控制器命名空间
    'controllerNamespace' => 'www\controller',

    // 注册树
    'register'            => [

        // 配置
        'config'         => [
            // 类路径
            'class'    => 'express\base\Config',
            // 自动加载
            'autoload' => ['common'],
        ],

        // 路由
        'route'          => [
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
        'request'        => [
            // 类路径
            'class' => 'express\web\Request',
        ],

        // 响应
        'response'       => [
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

        // 请求
        'swooleRequest'  => [
            // 非单例模式
            'singleton' => false,
            // 类路径
            'class'     => 'express\swoole\Request',
        ],

        // 响应
        'swooleResponse' => [
            // 非单例模式
            'singleton' => false,
            // 类路径
            'class'     => 'express\swoole\Response',
            // 输出格式
            'format'    => express\swoole\Response::FORMAT_JSON,
        ],

        // 错误
        'error'          => [
            // 类路径
            'class'  => 'express\web\Error',
            // 输出格式
            'format' => express\web\Error::FORMAT_HTML,
        ],

    ],

];
