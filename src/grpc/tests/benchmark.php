<?php
require __DIR__ . '/../vendor/autoload.php';

class SayService implements \Php\Micro\Grpc\Greeter\SayInterface
{

    public function Hello(\Mix\Grpc\Context $context, \Php\Micro\Grpc\Greeter\Request $request): \Php\Micro\Grpc\Greeter\Response
    {
        $response = new \Php\Micro\Grpc\Greeter\Response();
        $response->setMsg(sprintf('hello, %s', $request->getName()));
        return $response;
    }

}

Swoole\Coroutine\run(function () {
    // grpc
    $grpc = new Mix\Grpc\Server();
    $grpc->register(SayService::class);
    $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9597, false);
    $server->set([
        'open_http2_protocol' => true,
        'http_compression' => false,
    ]);
    $server->handle('/', $grpc->handler());
    go(function () use ($server) {
        $server->start();
    });

    // http
    // curl http://127.0.0.1:9596/benchmark
    // wrk -c100 -d15 http://127.0.0.1:9596/benchmark
    $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9596, false);
    $client = new Mix\Grpc\Client('127.0.0.1', 9597);
    $server->handle('/benchmark', function ($req, $resp) use ($client) {
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
});

