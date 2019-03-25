<?php

namespace Console\Commands;

use Mix\Concurrent\Sync\WaitGroup;

/**
 * Class WaitGroupCommand
 * @package Console\Commands
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class WaitGroupCommand
{

    /**
     * 主函数
     */
    public function main()
    {
        xgo(function () {
            $ws = WaitGroup::new();
            for ($i = 0; $i < 2; $i++) {
                $ws->add(1);
                xgo([$this, 'foo'], $ws);
            }
            $ws->wait();
            println('All done!');
        });
    }

    /**
     * 查询数据
     * @param WaitGroup $ws
     */
    public function foo(WaitGroup $ws)
    {
        xdefer(function () use ($ws) {
            $ws->done();
        });
        println('work');
        //throw new \RuntimeException('ERROR');
    }

}
