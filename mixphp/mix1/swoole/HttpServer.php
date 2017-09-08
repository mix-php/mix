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
    public $virtualHosts = [];

    // SwooleHttpServer对象
    private $server;

    // 进程名称
    private $processLabel;

    // 初始化
    public function init()
    {
        $this->server       = new \swoole_http_server($this->host, $this->port);
        $this->processLabel = "{$this->host}:{$this->port} {$this->virtualHost['hostname']}";
    }

    // 主进程启动事件
    private function onStart()
    {
        $this->server->on('Start', function ($server) {
            // 进程命名
            swoole_set_process_name("mixhttpd {$this->processLabel} master");
        });
    }

    // 管理进程启动事件
    private function onManagerStart()
    {
        $this->server->on('ManagerStart', function ($server) {
            // 进程命名
            swoole_set_process_name("mixhttpd {$this->processLabel} manager");
        });
    }

    // 工作进程启动事件
    private function onWorkerStart()
    {
        $this->server->on('WorkerStart', function ($server, $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                swoole_set_process_name("mixhttpd {$this->processLabel} worker");
            } else {
                swoole_set_process_name("mixhttpd {$this->processLabel} task");
            }
            // 实例化Apps
            \Mix::$apps = [];
            foreach ($this->virtualHosts as $host => $virtualHost) {
                $config       = require $virtualHost['config'];
                \Mix::$apps[$host] = new $virtualHost['class']($config);
            }
        });
    }

    // 请求事件
    private function onRequest()
    {
        $this->server->on('request', function ($request, $response) {
            $host = $request->header['host'];
            // 执行请求
            try {             

                \Mix::$app->error->register();
                \Mix::$app->request->setRequester($request);
                \Mix::$app->response->setResponder($response);
                
                if ($hostname == $this->virtualHost['hostname']) {
                    $this->app->run($request, $response);
                } else if ($this->virtualHost['hostname'] == '*') {
                    $this->app->run($request, $response);
                } else {
                    throw new \mix\exception\HttpException("VirtualHost Not Found", 404);
                }
            } catch (\Exception $e) {
                \Mix::$app->error->appException($e);
            }
        });
    }

    // 执行任务事件
    private function onTask()
    {
        $this->server->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
        });
    }

    // 任务结束事件
    private function onFinish()
    {
        $this->server->on('Finish', function ($server, $taskId, $data) {
        });
    }

    // 启动服务
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
