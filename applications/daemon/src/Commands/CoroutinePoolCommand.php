<?php

namespace Daemon\Commands;

use Mix\Concurrent\Dispatcher;
use Mix\Console\Command;
use Mix\Core\Channel;
use Mix\Helpers\ProcessHelper;

/**
 * 协程池范例
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class CoroutinePoolCommand extends Command
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
        tgo(function () {
            $maxWorkers = 20;
            $maxQueue   = 20;
            $jobQueue   = new Channel($maxQueue);
            $dispatch   = new Dispatcher([
                'jobQueue'   => $jobQueue,
                'maxWorkers' => $maxWorkers,
            ]);
            $dispatch->start();
            // 投放任务
            $redis = \Mix\Redis\Coroutine\RedisConnection::newInstance();
            while (true) {
                if ($this->quit) {
                    $dispatch->stop();
                    return;
                }
                try {
                    $data = $redis->brPop('test', 3);
                } catch (\Throwable $e) {
                    $dispatch->stop();
                    return;
                }
                if (!$data) {
                    continue;
                }
                $job = [[$this, 'call'], [array_pop($data)]];
                $jobQueue->push($job);
            }
        });
        // 捕获信号
        ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->quit = true;
            ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], null);
        });
        swoole_event_wait();
    }

    /**
     * 回调函数
     * 在 $maxWorkers 数量的协程之中并行执行
     * @param $i
     */
    public function call($data)
    {
        var_dump($data);
    }

}
