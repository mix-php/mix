<?php
declare(strict_types=1);

use Mix\Sync\WaitGroup;
use Mix\WorkerPool\AbstractWorker;
use Mix\WorkerPool\WorkerDispatcher;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;

final class MainTest extends TestCase
{

    public function test(): void
    {
        $func = function () {
            $maxWorkers = 20;
            $maxQueue   = 10;
            $jobQueue   = new Channel($maxQueue);
            $dispatcher = new WorkerDispatcher($jobQueue, $maxWorkers, FooWorker::class, 123);

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

    public function testOld(): void
    {
        $func = function () {
            $maxWorkers = 20;
            $maxQueue   = 10;
            $jobQueue   = new Channel($maxQueue);
            $dispatcher = new \Mix\WorkerPool\WorkerPoolDispatcher($jobQueue, $maxWorkers);

            go(function () use ($jobQueue, $dispatcher) {
                // 投放任务
                for ($i = 0; $i < 1000; $i++) {
                    $jobQueue->push($i);
                }
                // 停止
                $dispatcher->stop();
            });

            $dispatcher->start(FooWorker::class);
        };
        run($func);
    }

}

class FooWorker extends AbstractWorker
{

    public $foo;

    /**
     * FooWorker constructor.
     * @param null $foo
     */
    public function __construct($foo = null)
    {
        $this->foo = $foo;
    }

    /**
     * @param $data
     */
    public function do($data)
    {
        usleep(10000); // 测试队列消费缓慢的情况
    }

}
