<?php

/**
 * HttpServer类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\swoole;

use mix\base\Object;

class HttpServer extends Object
{

    // 主机
    public $host;

    // 主机
    public $port;

    // 运行时的各项参数
    public $setting = [];

    // 虚拟主机
    public $virtualHost = [];

    // SwooleHttpServer对象
    private $server;

    // 初始化
    public function init()
    {
        $this->server = new \swoole_http_server($this->host, $this->port);
    }

    private function onStart()
    {
        $this->server->on('Start', function ($server) {
            // 进程命名
            swoole_set_process_name("mixhttpd {$server->host}:{$server->port} master");
            echo 'onStart' . PHP_EOL;
        });
    }

    private function onManagerStart()
    {
        $this->server->on('ManagerStart', function ($server) {
            // 进程命名
            swoole_set_process_name("mixhttpd {$server->host}:{$server->port} manager");
            echo 'onManagerStart' . PHP_EOL;
        });
    }

    private function onWorkerStart()
    {
        $this->server->on('WorkerStart', function ($server, $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                swoole_set_process_name("mixhttpd {$server->host}:{$server->port} worker");
            } else {
                swoole_set_process_name("mixhttpd {$server->host}:{$server->port} task");
            }
            // 实例化App
            $hostname = array_keys($this->virtualHost);
            $hostname = array_shift($hostname);
            $virtualHost = array_shift($this->virtualHost);
            $config = require $virtualHost['config'];
            $this->app = [$hostname => new $virtualHost['class']($config)];
            echo 'onWorkerStart' . PHP_EOL;
        });
    }

    private function onRequest()
    {
        $this->server->on('request', function ($request, $response) {
            $hostname = $request->header['host'];
            // 执行请求
            try {
                \Mix::$app->request->setRequester($request);
                \Mix::$app->response->setResponder($response);
                \Mix::$app->error->register();
                if (array_key_exists($hostname, $this->app)) {
                    $this->app[$hostname]->run($request, $response);
                } else if (isset($this->app['*'])) {
                    $this->app['*']->run($request, $response);
                } else {
                    throw new \mix\exception\HttpException("VirtualHost Not Found", 404);
                }
            } catch (\Exception $e) {
                \Mix::$app->error->appException($e);
            }
        });
    }

    private function onTask()
    {
        $this->server->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
            echo 'onTask' . PHP_EOL;
        });
    }

    private function onFinish()
    {
        $this->server->on('Finish', function ($server, $taskId, $data) {
            echo 'onFinish' . PHP_EOL;
        });
    }

    public function start()
    {
        $this->onStart();
        $this->onManagerStart();
        $this->onWorkerStart();
        $this->onRequest();
        $this->onTask();
        $this->onFinish();
        $this->server->set($this->setting);
        $this->server->start();
    }

}
