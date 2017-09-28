<?php

namespace mixhttpd\server;

use mix\base\Object;
use mix\swoole\Application;

/**
 * HttpServer类
 * @author 刘健 <coder.liu@qq.com>
 */
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
    protected $server;

    // 进程名称
    protected $processLabel;

    // 初始化
    public function init()
    {
        $this->server = new \swoole_http_server($this->host, $this->port);
        $this->processLabel = "{$this->host}:{$this->port}";
        // 新建日志目录
        if (isset($this->setting['log_file'])) {
            $dir = dirname($this->setting['log_file']);
            is_dir($dir) or mkdir($dir);
        }
    }

    // 主进程启动事件
    protected function onStart()
    {
        $this->server->on('Start', function ($server) {
            // 进程命名
            swoole_set_process_name("mixhttpd {$this->processLabel} master");
        });
    }

    // 管理进程启动事件
    protected function onManagerStart()
    {
        $this->server->on('ManagerStart', function ($server) {
            // 进程命名
            swoole_set_process_name("mixhttpd {$this->processLabel} manager");
        });
    }

    // 工作进程启动事件
    protected function onWorkerStart()
    {
        $this->server->on('WorkerStart', function ($server, $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                swoole_set_process_name("mixhttpd {$this->processLabel} worker");
            } else {
                swoole_set_process_name("mixhttpd {$this->processLabel} task");
            }
            // 实例化Apps
            $apps = [];
            foreach ($this->virtualHosts as $host => $configFile) {
                $config = require $configFile;
                $apps[$host] = new Application($config);
            }
            \Mix::setApps($apps);
        });
    }

    // 请求事件
    protected function onRequest()
    {
        $this->server->on('request', function ($request, $response) {
            \Mix::setHost($request->header['host']);
            // 执行请求
            try {
                \Mix::app()->error->register();
                \Mix::app()->request->setRequester($request);
                \Mix::app()->response->setResponder($response);
                \Mix::app()->run($request);
            } catch (\Exception $e) {
                \Mix::app()->error->appException($e);
            }
        });
    }

    // 欢迎信息
    protected function welcome()
    {
        $swooleVersion = swoole_version();
        $phpVersion = PHP_VERSION;
        echo <<<EOL
                           _____
_______ ___ _____ ___ _____  / /_  ____
__/ __ `__ \/ /\ \/ / / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\/ .___/_/ /_/ .___/
                   /_/         /_/

Server     Name: mixhttpd
PHP     Version: {$phpVersion}
Swoole  Version: {$swooleVersion}
Listen     Addr: {$this->host}
Listen     Port: {$this->port}

EOL;
    }

    // 启动服务
    public function start()
    {
        $this->welcome();
        $this->onStart();
        $this->onManagerStart();
        $this->onWorkerStart();
        $this->onRequest();
        $this->server->set($this->setting);
        $this->server->start();
    }

}
