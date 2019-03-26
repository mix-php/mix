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
            $wg = WaitGroup::new();
            for ($i = 0; $i < 2; $i++) {
                $wg->add(1);
                xgo([$this, 'foo'], $wg);
            }
            $wg->wait();
            println('All done!');
        });
    }

    /**
     * 查询数据
     * @param WaitGroup $wg
     */
    public function foo(WaitGroup $wg)
    {
        xdefer(function () use ($wg) {
            $wg->done();
        });
        println('work');
        //throw new \RuntimeException('ERROR');
    }

}
