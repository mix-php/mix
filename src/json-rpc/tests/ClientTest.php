<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{

    public function testRequest(): void
    {
        $_this     = $this;
        $scheduler = new \Swoole\Coroutine\Scheduler;
        $scheduler->set([
            'hook_flags' => SWOOLE_HOOK_ALL,
        ]);
        $scheduler->add(function () use ($_this) {
            // server
            $server  = new \Mix\JsonRpc\Server('127.0.0.1', 9234);
            $service = new Calculator();
            $server->register($service);
            go(function () use ($server) {
                $server->start();
            });
            // client
            $client = new \Mix\JsonRpc\Client([
                'connection' => new \Mix\JsonRpc\Connection('127.0.0.1', 9234),
            ]);

            // 方法不存在
            $response = $client->call((new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('None.None', [1, 3], 0));
            var_dump(json_encode($response));
            $_this->assertNotNull($response->error);

            // 单个返回值
            $response = $client->call((new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [1, 3], 0));
            var_dump(json_encode($response));
            $_this->assertEquals($response->result[0], 4);

            // 多个返回值
            $response = $client->call((new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Plus', [1, 3], 0));
            var_dump(json_encode($response));
            $_this->assertEquals($response->result[0], 2);
            $_this->assertEquals($response->result[1], 4);

            // 批量调用
            $responses = $client->callMultiple((new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [1, 3], 10001), (new \Mix\JsonRpc\Factory\RequestFactory)->createRequest('Calculator.Sum', [2, 3], 10002));
            var_dump(json_encode($responses));
            $_this->assertEquals($responses[0]->result[0], 4);
            $_this->assertEquals($responses[1]->result[0], 5);

            $server->shutdown();
        });
        $scheduler->start();
    }

}

class Calculator
{
    public function Sum(int $a, int $b): int
    {
        return array_sum([$a, $b]);
    }

    public function Plus(int $a, int $b): array
    {
        return [++$a, ++$b];
    }
}
