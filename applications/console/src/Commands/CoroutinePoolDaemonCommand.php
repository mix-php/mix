<?php

namespace Console\Commands;

use Console\Libraries\CoroutinePoolDaemonWorker;
use Mix\Concurrent\CoroutinePool\Dispatcher;
use Mix\Console\CommandLine\Flag;
use Mix\Core\Coroutine\Channel;
use Mix\Core\Event;
use Mix\Helper\ProcessHelper;

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
            $dispatch->start(CoroutinePoolDaemonWorker::class);
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
        // 等待事件
        Event::wait();
    }

}
