<?php

namespace Console\Commands;

use Mix\Console\Command;
use Mix\Core\Channel;

/**
 * 协程范例
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class CoroutineCommand extends Command
{

    /**
     * 主函数
     */
    public function main()
    {
        xgo(function () {
            $time = time();

            list($foo, $bar) = [$this->foo(), $this->bar()];
            list($fooResult, $barResult) = [$foo->pop(), $bar->pop()];

            println('Total time: ' . (time() - $time));
            var_dump($fooResult);
            var_dump($barResult);
        });
    }

    /**
     * 查询数据
     * @return Channel
     */
    public function foo()
    {
        $chan = new Channel();
        xgo(function () use ($chan) {
            $pdo    = app()->pdoPool->getConnection();
            $result = $pdo->createCommand('select sleep(5)')->queryAll();
            $chan->push($result);
        });
        return $chan;
    }

    /**
     * 查询数据
     * @return Channel
     */
    public function bar()
    {
        $chan = new Channel();
        xgo(function () use ($chan) {
            $pdo    = app()->pdoPool->getConnection();
            $result = $pdo->createCommand('select sleep(2)')->queryAll();
            $chan->push($result);
        });
        return $chan;
    }

}
