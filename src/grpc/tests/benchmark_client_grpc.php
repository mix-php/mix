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

// grpc
Swoole\Coroutine\run(function () {
    $grpc = new Mix\Grpc\Server();
    $grpc->register(SayService::class);
    $server = new Swoole\Coroutine\Http\Server('0.0.0.0', 9597, false);
    $server->set([
        'open_http2_protocol' => true,
        'http_compression' => false,
    ]);
    $server->handle('/', $grpc->handler());
    $server->start();
});

