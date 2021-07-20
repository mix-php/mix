<?php
declare(strict_types=1);

use Mix\WorkerPool\WorkerPool;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $func = function () {
            $maxWorkers = 20;
            $maxQueue = 10;
            $jobQueue = new Channel($maxQueue);
            $dispatcher = new WorkerPool($jobQueue, $maxWorkers, new FooHandler());

            go(function () use ($jobQueue, $dispatcher) {
                // 投放任务
                for ($i = 0; $i < 1000; $i++) {
                    $jobQueue->push($i);
                }
                // 停止
                $dispatcher->stop();
            });

            $dispatcher->run(); // 阻塞代码，直到任务全部执行完成并且全部 Worker 停止
        };
        run($func);
    }

}

class FooHandler implements \Mix\WorkerPool\RunInterface
{

    public function do($data): void
    {
        usleep(10000); // 测试队列消费缓慢的情况
    }

}
