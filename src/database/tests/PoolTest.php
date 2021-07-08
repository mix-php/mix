<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PoolTest extends TestCase
{

    public function testConnReturn(): void
    {
        $_this = $this;
        $func = function () use ($_this) {
            $db = db();
            $db->startPool(1, 1);
            for ($i = 0; $i < 100; $i++) {
                go(function () use ($db, $i) {
                    $stat = $db->raw('select sleep(0.1)')->statement();
                    while ($row = $stat->fetch()) {
                        usleep(100000);
                    }
                    // echo sprintf("%d: %s\n", $i, json_encode($db->poolStats()));
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
            $db = db();
            $max = swoole_cpu_num() * 2;
            $db->startPool($max / 2, $max / 2);
            $time = microtime(true);
            $chan = new \Swoole\Coroutine\Channel();
            for ($i = 0; $i < $max; $i++) {
                go(function () use ($db, $chan) {
                    $db->raw('select sleep(1)')->queryAll();
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
            $db = db();
            $db->setMaxLifetime(1);

            $conn = $db->borrow();
            $id   = spl_object_hash($conn);
            $conn = null;
            sleep(1);
            $conn = $db->borrow();
            $id1  = spl_object_hash($conn);

            $_this->assertNotEquals($id, $id1);
        };
        swoole_run($func);
    }

    public function testWaitTimeout(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $db = db();
            $db->setMaxOpenConns(1);
            $db->setWaitTimeout(0.001);

            $conn = $db->borrow();
            try {
                $db->borrow();
            } catch (\Throwable $exception) {
                $_this->assertContains('Wait timeout', $exception->getMessage());
            }
        };
        swoole_run($func);
    }
    */

}
