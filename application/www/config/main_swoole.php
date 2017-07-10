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
        'request'  => [
            // 非单例模式
            'singleton' => false,
            // 类路径
            'class'     => 'express\swoole\Request',
        ],

        // 响应
        'response' => [
            // 非单例模式
            'singleton' => false,
            // 类路径
            'class'     => 'express\swoole\Response',
            // 输出格式
            'format'    => express\swoole\Response::FORMAT_JSON,
        ],

    ],

];
