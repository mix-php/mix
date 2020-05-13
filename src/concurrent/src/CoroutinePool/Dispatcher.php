<?php

namespace Mix\Concurrent\CoroutinePool;

use Mix\Concurrent\Exception\TypeException;
use Swoole\Coroutine\Channel;
use Mix\Concurrent\Timer;
use Mix\Concurrent\Coroutine;

/**
 * Class Dispatcher
 * @package Mix\Concurrent
 * @author liu,jian <coder.keda@gmail.com>
 */
class Dispatcher
{

    /**
     * @var Channel
     */
    public $jobQueue;

    /**
     * 最大工人数
     * @var int
     */
    public $maxWorkers;

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
     * Dispatcher constructor.
     * @param Channel $jobQueue
     * @param int $maxWorkers
     */
    public function __construct(Channel $jobQueue, int $maxWorkers)
    {
        $this->jobQueue   = $jobQueue;
        $this->maxWorkers = $maxWorkers;
        $this->workerPool = new Channel($this->maxWorkers);
        $this->quit       = new Channel();
    }

    /**
     * 启动
     * @param string $worker
     */
    public function start(string $worker)
    {
        if (!is_subclass_of($worker, AbstractWorker::class)) {
            throw new TypeException("{$worker} type is not '" . AbstractWorker::class . "'");
        }
        for ($i = 0; $i < $this->maxWorkers; $i++) {
            /** @var AbstractWorker $worker */
            $worker          = new $worker($this->workerPool);
            $this->workers[] = $worker;
            $worker->start();
        }
        $this->dispatch();
    }

    /**
     * 派遣
     */
    public function dispatch()
    {
        Coroutine::create(function () {
            while (true) {
                $data = $this->jobQueue->pop();
                if ($data === false) {
                    return;
                }
                $jobChannel = $this->workerPool->pop();
                $jobChannel->push($data);
            }
        });
        Coroutine::create(function () {
            $this->quit->pop();
            $timer = new Timer();
            $timer->tick(100, function () use ($timer) {
                if ($this->jobQueue->stats()['queue_num'] > 0) {
                    return;
                }
                $timer->clear();
                foreach ($this->workers as $worker) {
                    $worker->stop();
                }
                $this->jobQueue->close();
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
