<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mix\Time\Time;

final class TimeTest extends TestCase
{

    public function testTimer(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $timer = Time::newTimer(1 * Time::MILLISECOND);
            xgo(function () use ($timer, $_this) {
                $ts = $timer->channel()->pop();
                $_this->assertTrue(is_numeric($ts));
            });
        };
        run($func);
    }

    public function testTicker(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $ticker = Time::newTicker(1 * Time::MILLISECOND);
            xgo(function () use ($ticker, $_this) {
                $count = 0;
                while (true) {
                    $ts = $ticker->channel()->pop();
                    $_this->assertTrue(is_numeric($ts));
                    if ($count == 3) {
                        $_this->assertEquals(3, $count);
                        $ticker->stop();
                        return;
                    }
                    $count++;
                }
            });
        };
        run($func);
    }

}
