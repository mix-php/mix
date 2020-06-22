<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mix\Time\Time;
use Mix\Select\Select;

final class ContextTest extends TestCase
{

    // 测试 CancelContext
    public function testA(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $ctx    = new Mix\Context\Context();
            $cancel = $ctx->withCancel();

            xgo(function () use ($ctx, $_this) {
                while (true) {
                    Time::sleep(1 * Time::MILLISECOND);

                    if ((new Select(
                        Select::case(Select::pop($ctx->done()), function ($value) {
                            return Select::BREAK;
                        }),
                        Select::default(function () {
                        })
                    ))->run()->break()) {
                        $_this->assertTrue(true);
                        return;
                    }
                }
            });

            Time::sleep(10 * Time::MILLISECOND);
            $cancel();
        };
        run($func);
    }

}
