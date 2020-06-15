<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mix\Select\Select;
use Mix\Time\Timer;
use Mix\Time\Time;

final class SelectTest extends TestCase
{
//
//    // 两个case可执行，随机执行一个
//    public function testA(): void
//    {
//        $_this = $this;
//        $func  = function () use ($_this) {
//            $result = [];
//            while (true) {
//
//                $c1 = new \Mix\Coroutine\Channel();
//                $c1->push(1);
//                $c2 = new \Mix\Coroutine\Channel();
//                $c2->push(2);
//
//                (new Select(
//                    Select::case(Select::pop($c1), function ($value) use (&$result) {
//                        $result[$value] = "";
//                    }),
//                    Select::case(Select::pop($c2), function ($value) use (&$result) {
//                        $result[$value] = "";
//                    })
//                ))->run();
//
//                if (count($result) == 2) {
//                    break;
//                }
//            }
//            $_this->assertEquals(count($result), 2);
//        };
//        run($func);
//    }
//
//    // case 都不可执行，执行 default
//    public function testB(): void
//    {
//        $_this = $this;
//        $func  = function () use ($_this) {
//            $result = null;
//
//            $c1 = new \Mix\Coroutine\Channel();
//            $c2 = new \Mix\Coroutine\Channel();
//            (new Select(
//                Select::case(Select::pop($c1), function ($value) use (&$result) {
//                    $result = $value;
//                }),
//                Select::case(Select::pop($c2), function ($value) use (&$result) {
//                    $result = $value;
//                }),
//                Select::default(function () use (&$result) {
//                    $result = 3;
//                })
//            ))->run();
//
//            $_this->assertEquals($result, 3);
//        };
//        run($func);
//    }

    //
    public function testC(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = [];

            $c1 = new \Mix\Coroutine\Channel();
            $c2 = new \Mix\Coroutine\Channel();

//            $func = null;
//            $func = function() use ($c1, &$func) {
//                sleep(1);
//                $c1->push("aaaaaaaa");
//                \Swoole\Timer::after(1000, $func);
//            };
//            \Swoole\Timer::after(1000, $func);

            xgo(function () use ($c1, $c2) {
                $timer = new Timer(1 * Time::SECOND);
                for ($i = 0; $i < 3; $i++) {
                    $timer->channel()->pop();
                    $timer->reset(1 * Time::SECOND);
                    
                    if ($i % 2 == 0) {
                        var_dump(111111);
                        $c1->push(1);
                    } else {
                        var_dump(2222222);
                        $c2->push(2);
                    }
                }
            });

            for ($i = 0; $i < 3; $i++) {
                (new Select(
                    Select::case(Select::pop($c1), function ($value) use (&$result) {
                        var_dump($value);
                        $result[] = $value;
                    }),
                    Select::case(Select::pop($c2), function ($value) use (&$result) {
                        var_dump($value);
                        $result[] = $value;
                    })
                ))->run();
            }

            var_dump($result);

//            $_this->assertEquals(count($result), 1);
        };
        run($func);
    }

}
