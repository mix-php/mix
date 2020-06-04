<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PoolTest extends TestCase
{
    
    // 功能测试
    public function test(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $db   = db();
            $max  = $db->maxActive * 2;
            $time = time();
            $chan = new \Swoole\Coroutine\Channel();
            for ($i = 0; $i < $max; $i++) {
                go(function () use ($db, $chan) {
                    $db->prepare('select sleep(2)')->queryAll();
                    $chan->push(true);
                });
            }
            for ($i = 0; $i < $max; $i++) {
                $chan->pop();
            }
            $duration = time() - $time;
            $_this->assertTrue($duration - 4 < 1 && $duration - 4 >= 0);
        };
        run($func);
    }

    // 测试协程外部析构
    public function testDestruct(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $GLOBALS['conn'] = conn();
        };
        run($func);
        $_this->assertTrue(true);
    }

}
