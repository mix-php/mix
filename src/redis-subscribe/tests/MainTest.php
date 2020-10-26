<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $func = function () {
            $sub = new \Mix\Redis\Subscribe\Subscriber('127.0.0.1', 6379, '', 5);
            $sub->subscribe('foo', 'bar'); // 订阅失败将抛出异常
            $sub->subscribe('foo1', 'bar1');
            $sub->unsubscribe('foo', 'bar');

            go(function () {
                $redis = new \Redis();
                $redis->connect('127.0.0.1', 6379);
                $redis->publish('foo', 'foodata');
                $redis->publish('foo1', 'foo1data');
            });

            $chan = $sub->channel();
            while (true) {
                $data = $chan->pop();
                if (empty($data)) { // 手动close与redis异常断开都会导致返回false
                    if (!$sub->closed) {
                        // redis异常断开处理
                        var_dump('Redis connection is disconnected abnormally');
                    }
                    break;
                }
                $this->assertEquals($data->payload, 'foo1data');
                break;
            }
            $sub->close();
        };
        run($func);
    }

}
