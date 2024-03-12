<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class RespTest extends TestCase
{
    public function test(): void
    {
        $this->assertEquals(\Mix\Redis\Subscriber\Resp::build(null), "$-1\r\n");
        $this->assertEquals(\Mix\Redis\Subscriber\Resp::build(1), ":1\r\n");
        $this->assertEquals(\Mix\Redis\Subscriber\Resp::build('foo'), "$3\r\nfoo\r\n");
        $this->assertEquals(\Mix\Redis\Subscriber\Resp::build(['foo', 'bar']), "*2\r\n$3\r\nfoo\r\n$3\r\nbar\r\n");
        $this->assertEquals(\Mix\Redis\Subscriber\Resp::build([1, [2, '4'], 2, 'bar']), "*4\r\n:1\r\n*2\r\n:2\r\n$1\r\n4\r\n:2\r\n$3\r\nbar\r\n");
    }
}
