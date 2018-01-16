<?php

namespace mix\server;

use mix\base\BaseObject;
use mix\swoole\Application;

/**
 * Http服务器类
 * @author 刘健 <coder.liu@qq.com>
 */
class HttpServer extends BaseObject
{

    // 主机
    public $host;

    // 端口
    public $port;

    // 运行时的各项参数
    public $setting = [];

    // 虚拟主机
    public $virtualHosts = [];

    // Server对象
    protected $server;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 实例化服务器
        $this->server = new \Swoole\Http\Server($this->host, $this->port);
        // 设置保留配置项
        $this->setting['pid_file'] = '/var/run/mix-httpd.pid';
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

    // 主进程启动事件
    protected function onStart()
    {
        $this->server->on('Start', function ($server) {
            // 进程命名
            stristr(PHP_OS, 'DAR') === false and swoole_set_process_name("mix-httpd: master {$this->host}:{$this->port}");
        });
    }

    // 管理进程启动事件
    protected function onManagerStart()
    {
        $this->server->on('ManagerStart', function ($server) {
            // 进程命名
            stristr(PHP_OS, 'DAR') === false and swoole_set_process_name("mix-httpd: manager");
        });
    }

    // 工作进程启动事件
    protected function onWorkerStart()
    {
        $this->server->on('WorkerStart', function ($server, $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                stristr(PHP_OS, 'DAR') === false and swoole_set_process_name("mix-httpd: worker #{$workerId}");
            } else {
                stristr(PHP_OS, 'DAR') === false and swoole_set_process_name("mix-httpd: task #{$workerId}");
            }
            // 错误处理注册
            \mix\web\Error::register();
            // 实例化Apps
            $apps = [];
            foreach ($this->virtualHosts as $host => $configFile) {
                $config = require $configFile;
                $app    = new Application($config);
                $app->loadAllComponent();
                $apps[$host] = $app;
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
                \Mix::app()->request->setRequester($request);
                \Mix::app()->response->setResponder($response);
                \Mix::app()->run();
            } catch (\Exception $e) {
                \Mix::app()->error->appException($e);
            }
        });
    }

    // 欢迎信息
    protected function welcome()
    {
        $swooleVersion = swoole_version();
        $phpVersion    = PHP_VERSION;
        echo <<<EOL
                           _____
_______ ___ _____ ___ _____  / /_  ____
__/ __ `__ \/ /\ \/ / / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\/ .___/_/ /_/ .___/
                   /_/         /_/


EOL;
        $this->send('Server    Name: mix-httpd');
        $this->send("PHP    Version: {$phpVersion}");
        $this->send("Swoole Version: {$swooleVersion}");
        $this->send("Listen    Addr: {$this->host}");
        $this->send("Listen    Port: {$this->port}");
    }

    // 发送至屏幕
    public function send($msg)
    {
        $time = date('Y-m-d H:i:s');
        echo "[{$time}] " . $msg . PHP_EOL;
    }

}
