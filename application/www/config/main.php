<?php

// APP配置文件
return [

    'basePath' => dirname(__DIR__) . DS,

    // 注册树
    'register' => [

        // 配置
        'config'  => [
            // 类路径
            'class'    => 'express\base\Config',
            // 自动加载
            'autoload' => ['common'],
        ],

        // 路由
        'route'   => [
            // 类路径
            'class'               => 'express\base\Route',
            // 控制器命名空间
            'controllerNamespace' => 'www\controller',
            // 默认变量规则
            'defaultPattern'      => '[\w-]+',
            // 路由变量规则
            'patterns'            => [
                'id' => '\d+',
            ],
            // 路由规则
            'rules'               => [

            ],
        ],

        // 请求
        'request' => [
            // 类路径
            'class' => 'express\web\Request',
        ],

    ],

];
