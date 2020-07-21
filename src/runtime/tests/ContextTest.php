<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mix\Time\Time;

final class ContextTest extends TestCase
{

    // 测试 ValueContext
    public function testWithValue(): void
    {
        $ctx = new Mix\Context\Context();
        $ctx->withValue('foo', 'bar');
        $value = $ctx->value('foo');
        $this->assertEquals('bar', $value);
    }

    // 测试 CancelContext
    public function testWithCancel(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $ctx       = new Mix\Context\Context();
            $cancelCtx = $ctx->withCancel();

            xgo(function () use ($cancelCtx, $_this) {
                while (true) {
                    Time::sleep(1 * Time::MILLISECOND);

                    if (select(
                        select_case(select_pop($cancelCtx->done()), function ($value) {
                            return SELECT_BREAK;
                        }),
                        select_default(function () {
                        })
                    )->run()->break()) {
                        $_this->assertTrue(true);
                        return;
                    }
                }
            });

            Time::sleep(1 * Time::MILLISECOND);
            $cancelCtx->cancel();
        };
        run($func);
    }

    // 测试 TimeoutContext
    public function testWithTimeout(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $num        = 0;
            $ctx        = new Mix\Context\Context();
            $timeoutCtx = $ctx->withTimeout(3 * Time::MILLISECOND);

            xgo(function () use ($timeoutCtx, $_this, &$num) {
                while (true) {
                    Time::sleep(1 * Time::MILLISECOND); // 这里实际基本等于两倍

                    if (select(
                        select_case(select_pop($timeoutCtx->done()), function ($value) use (&$num) {
                            return SELECT_BREAK;
                        }),
                        select_default(function () use (&$num) {
                            ++$num;
                        })
                    )->run()->break()) {
                        $_this->assertTrue($num <= 2); // 毫秒sleep不准，会小于2
                        return;
                    }
                }
            });

            Time::sleep(10 * Time::MILLISECOND);
            $timeoutCtx->cancel();
        };
        run($func);
    }

}
