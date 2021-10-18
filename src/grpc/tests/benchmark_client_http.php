<?php
require __DIR__ . '/../vendor/autoload.php';

// http
// curl http://127.0.0.1:9596/benchmark_client
// wrk -c100 -d15 http://127.0.0.1:9596/benchmark_client
$server = new Swoole\Http\Server('0.0.0.0', 9596);
$server->on('WorkerStart', function () {
    global $client;
    $client = new Mix\Grpc\Client('127.0.0.1', 9597);
});
$server->on('Request', function ($req, $resp) {
    global $client;
    try {
        $say = new \Php\Micro\Grpc\Greeter\SayClient($client);
        $request = new \Php\Micro\Grpc\Greeter\Request();
        $request->setName('xiaoming');
        $response = $say->Hello(new \Mix\Grpc\Context(), $request);
        $resp->end($response->getMsg());
    } catch (\Throwable $ex) {
        echo sprintf("Error: %s\n", $response->getMsg());
        $resp->end($ex->getMessage());
    }
});
$server->start();
