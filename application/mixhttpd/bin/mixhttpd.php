<?php

date_default_timezone_set('PRC');

class SwooleHttpServer extends \swoole_http_server
{

    public function registerApplication()
    {
        define('MIX_DEBUG', true);
        define('MIX_ENV', 'dev');
        define('DS', DIRECTORY_SEPARATOR);
        require __DIR__ . '/../../../vendor/autoload.php';
        require __DIR__ . '/../../../mixphp/mix1/Mix.php';
    }

    public function getApplicationInstance()
    {
        $config = require __DIR__ . '/../../swooleweb/config/main.php';
        return new mix\swoole\Application($config);
    }

    public function onStart()
    {
        $this->on('Start', function ($serv) {
            // 进程命名
            $hostname = "{$serv->host}:{$serv->port}";
            swoole_set_process_name("mixhttpd {$hostname} master");
            echo 'onStart' . PHP_EOL;
        });
    }

    public function onManagerStart()
    {
        $this->on('ManagerStart', function ($serv) {
            // 进程命名
            $hostname = "{$serv->host}:{$serv->port}";
            swoole_set_process_name("mixhttpd {$hostname} manager");
            echo 'onManagerStart' . PHP_EOL;
        });
    }

    public function onWorkerStart()
    {
        $this->on('WorkerStart', function ($serv, $workerId) {
            // 进程命名
            $hostname = "{$serv->host}:{$serv->port}";
            if ($workerId < $serv->setting['worker_num']) {
                swoole_set_process_name("mixhttpd {$hostname} worker");
            } else {
                swoole_set_process_name("mixhttpd {$hostname} task");
            }
            // 实例化App
            $serv->app = $this->getApplicationInstance();
            echo 'onWorkerStart' . PHP_EOL;
        });
    }

    public function onRequest()
    {
        $this->on('request', function ($request, $response) {
            // 执行请求
            try {
                $this->app->run($request, $response);
            } catch (\Exception $e) {
                \Mix::$app->error->appException($e);
            }
        });
    }

    public function onTask()
    {
        $this->on('Task', function ($serv, $taskId, $srcWorkerId, $data) {
            echo 'onTask' . PHP_EOL;
        });
    }

    public function onFinish()
    {
        $this->on('Finish', function ($serv, $taskId, $data) {
            echo 'onFinish' . PHP_EOL;
        });
    }

    public function prepare()
    {
        $this->registerApplication();
        $this->onStart();
        $this->onManagerStart();
        $this->onWorkerStart();
        $this->onRequest();
        $this->onTask();
        $this->onFinish();
        $this->set([
            'worker_num'      => 4,
            'task_worker_num' => 2,
        ]);
        return $this;
    }

}

(new SwooleHttpServer("192.168.181.130", 9501))->prepare()->start();
