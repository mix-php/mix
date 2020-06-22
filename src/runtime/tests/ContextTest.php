<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mix\Time\Time;
use Mix\Select\Select;

final class ContextTest extends TestCase
{

    //
    public function testA(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = null;

            $ctx    = new Mix\Context\Context();
            $cancel = $ctx->withCancel();

            xgo(function () use ($ctx, $result) {
                while (true) {
                    Time::sleep(1 * Time::MILLISECOND);

                    if ((new Select(
                        Select::case(Select::pop($ctx->done()), function ($value) {
                            return Select::BREAK;
                        }),
                        Select::default(function ($value) {
                        })
                    ))->run()->break()) {
                        $result = Select::BREAK;
                        return;
                    }
                }
            });

            Time::sleep(10 * Time::MILLISECOND);
            $cancel();
            Time::sleep(1 * Time::MILLISECOND);

            $_this->assertEquals($result, Select::BREAK);
        };
        run($func);
    }

}
