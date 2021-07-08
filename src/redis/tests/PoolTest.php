<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PoolTest extends TestCase
{

    public function testConnReturn(): void
    {
        $_this = $this;
        $func = function () use ($_this) {
            $rds = redis();
            $rds->startPool(1, 1);
            for ($i = 0; $i < 10; $i++) {
                go(function () use ($rds, $i) {
                    $rds->blPop('foo_list', 1);
                });
            }
            $_this->assertTrue(true);
        };
        swoole_co_run($func);
    }

    public function testMaxOpen(): void
    {
        $_this = $this;
        $func = function () use ($_this) {
            $rds = redis();
            $max = swoole_cpu_num() * 2;
            $rds->startPool($max / 2, $max / 2);
            $time = microtime(true);
            $chan = new \Swoole\Coroutine\Channel();
            for ($i = 0; $i < $max; $i++) {
                go(function () use ($rds, $chan) {
                    $rds->blPop('foo_list', 1);
                    $chan->push(true);
                });
            }
            for ($i = 0; $i < $max; $i++) {
                $chan->pop();
            }
            $duration = microtime(true) - $time;
            $_this->assertTrue($duration >= 2 && $duration <= 3);
        };
        swoole_co_run($func);
    }

    /*
    public function testMaxLifetime(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $redis = redis();
            $redis->setMaxLifetime(1);

            $conn = $redis->borrow();
            $id   = spl_object_hash($conn);
            $conn = null;
            sleep(1);
            $conn = $redis->borrow();
            $id1  = spl_object_hash($conn);

            $_this->assertNotEquals($id, $id1);
        };
        run($func);
    }

    public function testWaitTimeout(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $redis = redis();
            $redis->setMaxOpen(1);
            $redis->setWaitTimeout(0.001);

            $conn = $redis->borrow();
            try {
                $redis->borrow();
            } catch (\Throwable $exception) {
                $_this->assertContains('Wait timeout', $exception->getMessage());
            }
        };
        run($func);
    }
    */

}
