<?php

namespace Daemon\Commands;

use Mix\Concurrent\Dispatcher;
use Mix\Console\Command;
use Mix\Core\Channel;
use Mix\Helpers\ProcessHelper;
use Mix\Redis\Coroutine\RedisPool;

/**
 * 协程池范例
 * @author 刘健 <coder.liu@qq.com>
 */
class CoroutinePoolCommand extends Command
{

    /**
     * @var RedisPool
     */
    public $redisPool;

    /**
     * 主函数
     */
    public function main()
    {
        tgo(function () {
            $maxWorkers = 20;
            $maxQueue   = 10;
            $jobQueue   = new Channel($maxQueue);
            $dispatch   = new Dispatcher([
                'jobQueue'   => $jobQueue,
                'maxWorkers' => $maxWorkers,
            ]);
            $dispatch->start();
            // 投放任务
            $this->redisPool = app()->redisPool;
            while (true) {
                if (!isset($this->redisPool)) {
                    $dispatch->stop();
                    return;
                }
                $redis = $this->redisPool->getConnection();
                $data  = $redis->brpop('test', 10);
                if (!$data) {
                    continue;
                }
                $job = [[$this, 'call'], [array_pop($data)]];
                $jobQueue->push($job);
            }
        });
        ProcessHelper::signal([SIGHUP, SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->redisPool = null;
            swoole_timer_tick(1000, function () {
                $stats = \Swoole\Coroutine::stats();
                if ($stats['coroutine_num'] == 1) {
                    exit;
                }
            });
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
        println($data);
    }

}
