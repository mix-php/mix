<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GoServerTest extends TestCase
{

    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            exec('cd tests/goserver; go run *.go > /dev/null 2>&1 &');
            usleep(1000000);

            $client    = new Mix\Grpc\Client('127.0.0.1', 9596);
            $say  = new \Php\Micro\Grpc\Greeter\SayClient($client);
            $request = new \Php\Micro\Grpc\Greeter\Request();
            $request->setName('xiaoming');
            $response = $say->Hello(new \Mix\Grpc\Context(), $request);

            $_this->assertEquals($response->getMsg(), 'hello, xiaoming');

            $client->close();
            exec("ps -ef | grep \"greeter.pb\" | grep -v grep | awk '{print $2}' | xargs kill");
        };
        run($func);
    }

}
