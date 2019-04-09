<?php

namespace Console\Commands;

use Console\Libraries\Worker;
use Mix\Concurrent\CoroutinePool\Dispatcher;
use Mix\Core\Coroutine\Channel;
use Mix\Core\Event;

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
            $dispatch->start(Worker::class);
            // 投放任务
            for ($i = 0; $i < 1000; $i++) {
                $data = [
                    'id' => $i,
                ];
                $jobQueue->push($data);
            }
            // 停止
            $dispatch->stop();
        });
        Event::wait();
    }

}
