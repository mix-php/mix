<?php

namespace Console\Commands;

use Mix\Concurrent\CoroutinePool\Dispatcher;
use Mix\Core\Coroutine\Channel;

/**
 * Class CoroutinePoolCommand
 * @package Console\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class CoroutinePoolCommand
{

    /**
     * 主函数
     */
    public function main()
    {
        xgo(function () {
            $maxWorkers = 20;
            $maxQueue   = 10;
            $jobQueue   = new Channel($maxQueue);
            $dispatch   = new Dispatcher([
                'jobQueue'   => $jobQueue,
                'maxWorkers' => $maxWorkers,
            ]);
            $dispatch->start();
            // 投放任务
            for ($i = 0; $i < 1000; $i++) {
                $job = [[$this, 'call'], $i];
                $jobQueue->push($job);
            }
            // 停止
            $dispatch->stop();
        });
    }

    /**
     * 回调函数
     * 并行执行在 $maxWorkers 数量的协程之中
     * @param $i
     */
    public function call($i)
    {
        var_dump($i);
    }

}
