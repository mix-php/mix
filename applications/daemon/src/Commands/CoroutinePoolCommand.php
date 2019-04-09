<?php

namespace Daemon\Commands;

use Daemon\Libraries\CoroutinePoolWorker;
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
            $dispatch->start(CoroutinePoolWorker::class);
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
                $data = array_pop($data); // brPop命令最后一个键才是值
                $jobQueue->push($data);
            }
        });
    }

}
