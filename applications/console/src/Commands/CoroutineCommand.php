<?php

namespace Console\Commands;

use Mix\Console\Command;
use Mix\Core\Channel;
use Mix\Core\ChannelHook;

/**
 * 协程范例
 * @author 刘健 <coder.liu@qq.com>
 */
class CoroutineCommand extends Command
{

    /**
     * 主函数
     */
    public function main()
    {
        tgo(function () {
            $time = time();
            list($foo, $bar) = [$this->foo(), $this->bar()];
            list($fooResult, $barResult) = [$foo->pop(), $bar->pop()];
            println('Time: ' . (time() - $time));
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
        tgo(function (ChannelHook $hook) use ($chan) {
            $hook->install($chan);
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
        tgo(function (ChannelHook $hook) use ($chan) {
            $hook->install($chan);
            $pdo    = app()->pdoPool->getConnection();
            $result = $pdo->createCommand('select sleep(2)')->queryAll();
            $chan->push($result);
        });
        return $chan;
    }

}
