<?php

namespace Daemon\Commands;

use Mix\Concurrent\CoroutinePool\Dispatcher;
use Mix\Core\Coroutine\Channel;
use Mix\Helper\ProcessHelper;

/**
 * Class CoroutinePoolCommand
 * @package Daemon\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class CoroutinePoolCommand
{

    /**
     * 退出
     * @var bool
     */
    public $quit = false;

    /**
     * 主函数
     */
    public function main()
    {
        // 捕获信号
        ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->quit = true;
            ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], null);
        });
        // 协程池执行任务
        xgo(function () {
            $maxWorkers = 20;
            $maxQueue   = 20;
            $jobQueue   = new Channel($maxQueue);
            $dispatch   = new Dispatcher([
                'jobQueue'   => $jobQueue,
                'maxWorkers' => $maxWorkers,
            ]);
            $dispatch->start();
            // 投放任务
            $redis = app()->redisPool->getConnection();
            while (true) {
                if ($this->quit) {
                    $dispatch->stop();
                    return;
                }
                try {
                    $data = $redis->brPop(['test'], 3);
                } catch (\Throwable $e) {
                    $dispatch->stop();
                    return;
                }
                if (!$data) {
                    continue;
                }
                $job = [[$this, 'call'], array_pop($data)];
                $jobQueue->push($job);
            }
        });
    }

    /**
     * 回调函数
     * 在 $maxWorkers 数量的协程之中并行执行
     * @param $data
     */
    public function call($data)
    {
        var_dump($data);
    }

}
