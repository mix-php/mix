<?php

namespace Mix\WorkerPool;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;

/**
 * Class Worker
 * @package Mix\WorkerPool
 */
class Worker
{

    /**
     * @var Channel
     */
    protected $workerPool;

    /**
     * @var WaitGroup
     */
    protected $waitGroup;

    /**
     * @var \Closure|RunInterface
     */
    protected $run;

    /**
     * @var Channel
     */
    protected $jobChannel;

    /**
     * @var Channel
     */
    protected $quit;

    /**
     * Worker constructor.
     * @param Channel $workerPool
     * @param WaitGroup $waitGroup
     * @param \Closure|RunInterface $run
     */
    public function __construct(Channel $workerPool, WaitGroup $waitGroup, $run)
    {
        $this->workerPool = $workerPool;
        $this->waitGroup = $waitGroup;
        $this->run = $run;
        $this->jobChannel = new Channel();
        $this->quit = new Channel();
    }

    public function run()
    {
        $this->waitGroup->add(1);
        Coroutine::create(function () {
            Coroutine::defer(function () {
                $this->waitGroup->done();
            });
            while (true) {
                $this->workerPool->push($this->jobChannel);
                $data = $this->jobChannel->pop();
                if ($data === false) {
                    return;
                }
                if ($this->run instanceof \Closure) {
                    $run = $this->run;
                    $run($data);
                } else if ($this->run instanceof RunInterface) {
                    $run = $this->run;
                    $run->do($data);
                }
            }
        });
        Coroutine::create(function () {
            $this->quit->pop();
            $this->jobChannel->close();
        });
    }

    public function stop()
    {
        Coroutine::create(function () {
            $this->quit->push(true);
        });
    }

}
