<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTest extends TestCase
{

    public function testGetSet(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $redis = redis();
            $redis->set('foo', 'bar');
            $result = $redis->get('foo');
            $_this->assertContains('bar', $result);
        };
        run($func);
    }

    public function testMulti(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $redis = redis();
            $conn  = $redis->multi();
            $conn->set('foo2', "bar2");
            $conn->incr('foo1');
            $conn->incr('foo1');
            $result = $conn->exec();
            $_this->assertEquals(count($result), 3);
        };
        run($func);
    }

    public function testMultiEx(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $redis = redis();
            $redis->multi();
            $e = null;
            try {
                $redis->exec();
            } catch (\Throwable $ex) {
                $e = $ex;
            }
            $_this->assertNotNull($e);
        };
        run($func);
    }

    public function testDialer(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $redis = (new \Mix\Redis\Dialer())->dial(REDIS_HOST, REDIS_PORT, REDIS_PASSWORD, REDIS_DATABASE);
            $redis->set('foo', 'bar');
            $result = $redis->get('foo');
            $_this->assertContains('bar', $result);
        };
        run($func);
    }

}
