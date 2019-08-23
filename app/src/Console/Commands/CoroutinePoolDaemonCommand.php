<?php

namespace App\Console\Commands;

use App\Console\Workers\CoroutinePoolDaemonWorker;
use Mix\Concurrent\CoroutinePool\Dispatcher;
use Mix\Console\CommandLine\Flag;
use Mix\Concurrent\Coroutine\Channel;
use Mix\Helper\ProcessHelper;
use Mix\Redis\Pool\ConnectionPool;

/**
 * Class CoroutinePoolDaemonCommand
 * @package Daemon\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class CoroutinePoolDaemonCommand
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
        // 守护处理
        $daemon = Flag::bool(['d', 'daemon'], false);
        if ($daemon) {
            ProcessHelper::daemon();
        }
        // 捕获信号
        ProcessHelper::signal([SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->quit = true;
            ProcessHelper::signal([SIGINT, SIGTERM, SIGQUIT], null);
        });
        // 获取连接
        /** @var ConnectionPool $redisPool */
        $redisPool = context()->get('redisPool');
        $redis     = $redisPool->getConnection();
        // 协程池执行任务
        xgo(function () use ($redis) {
            $maxWorkers = 20;
            $maxQueue   = 20;
            $jobQueue   = new Channel($maxQueue);
            $dispatch   = new Dispatcher([
                'jobQueue'   => $jobQueue,
                'maxWorkers' => $maxWorkers,
            ]);
            $dispatch->start(CoroutinePoolDaemonWorker::class);
            // 投放任务
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
