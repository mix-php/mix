<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mix\Select\Select;
use Mix\Time\Timer;
use Mix\Time\Time;

final class SelectTest extends TestCase
{

    // 两个 case 可执行，随机执行一个
    public function testA(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = [];
            while (true) {

                $c1 = new \Mix\Coroutine\Channel();
                $c1->push(1);
                $c2 = new \Mix\Coroutine\Channel();
                $c2->push(2);

                (new Select(
                    Select::case(Select::pop($c1), function ($value) use (&$result) {
                        $result[$value] = "";
                    }),
                    Select::case(Select::pop($c2), function ($value) use (&$result) {
                        $result[$value] = "";
                    })
                ))->run();

                if (count($result) == 2) {
                    break;
                }
            }
            $_this->assertEquals(count($result), 2);
        };
        run($func);
    }

    // case 都不可执行，执行 default
    public function testB(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = null;

            $c1 = new \Mix\Coroutine\Channel();
            $c2 = new \Mix\Coroutine\Channel();
            (new Select(
                Select::case(Select::pop($c1), function ($value) use (&$result) {
                    $result = $value;
                }),
                Select::case(Select::pop($c2), function ($value) use (&$result) {
                    $result = $value;
                }),
                Select::default(function () use (&$result) {
                    $result = 3;
                })
            ))->run();

            $_this->assertEquals($result, 3);
        };
        run($func);
    }

    // 不带 default，两个 case 都是 pop
    public function testC(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = [];

            $c1 = new \Mix\Coroutine\Channel();
            $c2 = new \Mix\Coroutine\Channel();

            xgo(function () use ($c1, $c2) {
                $timer = new Timer(10 * Time::MILLISECOND);
                for ($i = 0; $i < 3; $i++) {
                    $timer->channel()->pop();
                    $timer->reset(10 * Time::MILLISECOND);

                    if ($i % 2 == 0) {
                        $c1->push(1);
                    } else {
                        $c2->push(2);
                    }
                }
            });

            for ($i = 0; $i < 3; $i++) {
                (new Select(
                    Select::case(Select::pop($c1), function ($value) use (&$result) {
                        $result[] = $value;
                    }),
                    Select::case(Select::pop($c2), function ($value) use (&$result) {
                        $result[] = $value;
                    })
                ))->run();
            }
            $_this->assertEquals($result, [1, 2, 1]);
        };
        run($func);
    }

}
