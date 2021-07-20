<?php

namespace Mix\WorkerPool;

use Mix\WorkerPool\Exception\TypeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;
use Swoole\Timer;

/**
 * Class WorkerPool
 * @package Mix\WorkerPool
 */
class WorkerPool
{

    /**
     * @var Channel
     */
    protected $jobQueue;

    /**
     * @var int
     */
    protected $maxWorkers;

    /**
     * @var \Closure|RunInterface
     */
    protected $run;

    /**
     * @var Channel
     */
    protected $workerPool;

    /**
     * @var array
     */
    protected $workers = [];

    /**
     * @var WaitGroup
     */
    protected $waitGroup;

    /**
     * @var Channel
     */
    protected $quit;

    /**
     * @var int
     */
    protected $timerId;

    /**
     * WorkerDispatcher constructor.
     * @param Channel $jobQueue
     * @param int $maxWorkers
     * @param \Closure|RunInterface $run
     * @throws TypeException
     */
    public function __construct(Channel $jobQueue, int $maxWorkers, $run)
    {
        $this->jobQueue = $jobQueue;
        $this->maxWorkers = $maxWorkers;
        $this->run = $run;
        $this->workerPool = new Channel($this->maxWorkers);
        $this->waitGroup = new WaitGroup();
        $this->quit = new Channel();

        if (!$run instanceof \Closure && !$run instanceof RunInterface) {
            throw new TypeException('The $run type is invalid');
        }
    }

    public function run()
    {
        $this->start();
        $this->wait();
    }

    public function start()
    {
        for ($i = 0; $i < $this->maxWorkers; $i++) {
            $worker = new Worker($this->workerPool, $this->waitGroup, $this->run);
            $this->workers[] = $worker;
            $worker->run();
        }
        $this->dispatch();
    }

    public function wait()
    {
        $this->waitGroup->wait();
    }

    public function stop()
    {
        Coroutine::create(function () {
            $this->quit->push(true);
        });
    }

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
            $this->timerId = Timer::tick(100, function () {
                if ($this->jobQueue->stats()['queue_num'] > 0) {
                    return;
                }
                Timer::clear($this->timerId);
                foreach ($this->workers as $worker) {
                    $worker->stop();
                }
                $this->jobQueue->close();
            });
        });
    }

}
