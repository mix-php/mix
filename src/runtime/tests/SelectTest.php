<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Mix\Select\Select;
use Mix\Time\Time;

final class SelectTest extends TestCase
{

    // 两个 case 可执行，随机执行一个
    public function testA(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = [];

            $c1 = new \Mix\Coroutine\Channel();
            $c2 = new \Mix\Coroutine\Channel();
            $c1->push(1);
            $c2->push(2);

            while (true) {
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
                $timer = Time::newTimer(1 * Time::MILLISECOND);
                for ($i = 0; $i < 3; $i++) {
                    $timer->channel()->pop();
                    $timer->reset(1 * Time::MILLISECOND);

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

    // 不带 default, case 的 pop、push 各一个
    public function testD(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = [];

            $c1 = new \Mix\Coroutine\Channel();
            $c1->push(0);

            for ($i = 0; $i < 10; $i++) {
                (new Select(
                    Select::case(Select::pop($c1), function ($value) use (&$result) {
                        $result[] = $value;
                    }),
                    Select::case(Select::push($c1, $i), function ($value) {
                    })
                ))->run();
            }

            $_this->assertEquals($result, [0, 1, 3, 5, 7]);
        };
        run($func);
    }

    // 中断循环
    public function testE(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = [];

            $c1    = new \Mix\Coroutine\Channel();
            $timer = Time::newTimer(1 * Time::MILLISECOND);

            for ($i = 0; $i < 10; $i++) {
                if ((new Select(
                    Select::case(Select::pop($c1), function ($value) {
                    }),
                    Select::case(Select::pop($timer->channel()), function ($value) use (&$result, $i) {
                        $result[] = $i;
                        return Select::BREAK;
                    })
                ))->run()->break()) {
                    break; // or return
                }
            }

            $_this->assertEquals($result, [0]);
        };
        run($func);
    }

    // 函数版本
    public function testF(): void
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = [];

            $c1 = new \Mix\Coroutine\Channel();
            $c1->push(0);

            for ($i = 0; $i < 10; $i++) {
                select(
                    select_case(select_pop($c1), function ($value) use (&$result) {
                        $result[] = $value;
                    }),
                    select_case(select_push($c1, $i), function ($value) {
                    }),
                    select_default(function () {
                    })
                )->run();
            }

            $_this->assertEquals($result, [0, 1, 3, 5, 7]);
        };
        run($func);
    }

    // 通道 close
    public function testG()
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $result = [];
            $c1     = new \Mix\Coroutine\Channel();
            $timer  = Time::newTimer(1 * Time::MILLISECOND);

            xgo(function () use ($c1, $timer) {
                $timer->channel()->pop();
                $c1->close();
            });

            while (true) {
                if ((new Select(
                    Select::case(Select::pop($c1), function ($value) use (&$result) {
                        $result[] = $value;
                        if ($result === [false, false]) {
                            return Select::BREAK;
                        }
                    })
                ))->run()->break()) {
                    break;
                }
            }

            $_this->assertEquals($result, [false, false]);
        };
        run($func);
    }

    // 没有定时器, close
    public function testH()
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $c1 = new \Mix\Coroutine\Channel();

            xgo(function () use ($c1) {
                $c1->close();
            });

            (new Select(
                Select::case(Select::pop($c1), function ($value) use ($_this) {
                    $_this->assertEquals($value, false);
                })
            ))->run();
        };
        run($func);
    }

    /*

    // 死锁 (Fatal error 无法捕获)
    public function testI()
    {
        $_this = $this;
        $func  = function () use ($_this) {
            $c1 = new \Mix\Coroutine\Channel();

            (new Select(
                Select::case(Select::pop($c1), function ($value) use ($_this) {
                    $_this->assertEquals($value, false);
                })
            ))->run();
        };
        run($func);
    }

    */

}
