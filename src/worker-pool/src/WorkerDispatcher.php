<?php

namespace Mix\WorkerPool;

use Mix\Sync\WaitGroup;
use Mix\Time\Time;
use Mix\WorkerPool\Exception\TypeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * Class WorkerDispatcher
 * @package Mix\WorkerPool
 * @author liu,jian <coder.keda@gmail.com>
 */
class WorkerDispatcher
{

    /**
     * @var Channel
     */
    protected $jobQueue;

    /**
     * 最大工人数
     * @var int
     */
    protected $maxWorkers;

    /**
     * 工作类
     * @var string
     */
    protected $workerClass;

    /**
     * 工作类构造参数
     * @var array
     */
    protected $constructorArgs;

    /**
     * 工作池
     * 内部数据的是Channel
     * @var Channel
     */
    protected $workerPool;

    /**
     * 工作者集合
     * @var array
     */
    protected $workers = [];

    /**
     * 退出
     * @var Channel
     */
    protected $quit;

    /**
     * WorkerDispatcher constructor.
     * @param Channel $jobQueue
     * @param int $maxWorkers
     * @param string $workerClass
     * @param mixed ...$constructorArgs
     * @throws TypeException
     */
    public function __construct(Channel $jobQueue, int $maxWorkers, string $workerClass, ...$constructorArgs)
    {
        $this->jobQueue        = $jobQueue;
        $this->maxWorkers      = $maxWorkers;
        $this->workerClass     = $workerClass;
        $this->constructorArgs = $constructorArgs;
        $this->workerPool      = new Channel($this->maxWorkers);
        $this->quit            = new Channel();

        if (!is_subclass_of($workerClass, AbstractWorker::class)) {
            throw new TypeException("{$workerClass} type is not '" . AbstractWorker::class . "'");
        }
    }

    /**
     * 启动
     * 阻塞代码，直到任务全部执行完成并且全部 Worker 停止
     */
    public function run()
    {
        $waitGroup   = new WaitGroup();
        $workerClass = $this->workerClass;
        for ($i = 0; $i < $this->maxWorkers; $i++) {
            /** @var AbstractWorker $worker */
            $worker = new $workerClass(...$this->constructorArgs);
            $worker->init($i, $this->workerPool, $waitGroup);
            $this->workers[] = $worker;
            $worker->run();
        }
        $this->dispatch();
        $waitGroup->wait();
    }

    /**
     * 派遣
     */
    protected function dispatch()
    {
        Coroutine::create(function () {
            while (true) {
                $data = $this->jobQueue->pop();
                if ($data === false) {
                    return;
                }
                $jobChannel = $this->workerPool->pop();
                if ($jobChannel === false) {
                    return;
                }
                $jobChannel->push($data);
            }
        });
        Coroutine::create(function () {
            $this->quit->pop();
            $ticker = Time::newTicker(100 * Time::MILLISECOND);
            Coroutine::create(function () use ($ticker) {
                while (true) {
                    $ticker->channel()->pop();
                    if ($this->jobQueue->stats()['queue_num'] > 0) {
                        continue;
                    }
                    $ticker->stop();
                    foreach ($this->workers as $worker) {
                        $worker->stop();
                    }
                    $this->jobQueue->close();
                    return;
                }
            });
        });
    }

    /**
     * 停止
     */
    public function stop()
    {
        Coroutine::create(function () {
            $this->quit->push(true);
        });
    }

}
