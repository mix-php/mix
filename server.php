<?php

class SwooleHttpServer extends \swoole_http_server
{

    public $app;

    public function loadApplication()
    {
        define('MIX_DEBUG', true);
        define('MIX_ENV', 'dev');
        define('DS', DIRECTORY_SEPARATOR);
        require __DIR__ . '/vendor/autoload.php';
        require __DIR__ . '/mixphp/mix1/Mix.php';
        $config = require __DIR__ . '/application/swooleweb/config/main.php';
        $this->app = new mix\swoole\Application($config);
    }

    public function onWorkerStart()
    {
        $this->on('WorkerStart', function ($serv, $worker_id) {
            var_dump($worker_id);
        });
    }

    public function onRequest()
    {
        $this->on('request', function ($request, $response) {
            try {
                $this->app->run($request, $response);
            } catch (\Exception $e) {
                \Mix::$app->error->appException($e);
            }
        });
    }

    public function prepare()
    {
        $this->loadApplication();
        $this->onWorkerStart();
        $this->onRequest();
        return $this;
    }

}

$http = new SwooleHttpServer("192.168.181.130", 9501);
$http->prepare()->start();
