<?php

namespace Mix\WorkerPool;

use Mix\Sync\WaitGroup;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

/**
 * Class AbstractWorker
 * @package Mix\WorkerPool
 * @author liu,jian <coder.keda@gmail.com>
 */
abstract class AbstractWorker
{

    /**
     * 工作池
     * @var Channel
     */
    protected $workerPool;

    /**
     * @var WaitGroup
     */
    protected $waitGroup;

    /**
     * 任务通道
     * @var Channel
     */
    protected $jobChannel;

    /**
     * 退出
     * @var Channel
     */
    protected $quit;

    /**
     * AbstractWorker constructor.
     * @param Channel $workerPool
     * @param WaitGroup $waitGroup
     */
    public function __construct(Channel $workerPool, WaitGroup $waitGroup)
    {
        $this->workerPool = $workerPool;
        $this->waitGroup  = $waitGroup;
        $this->jobChannel = new Channel();
        $this->quit       = new Channel();
    }

    /**
     * 处理
     * @param $data
     */
    abstract public function handle($data);

    /**
     * 启动
     * @deprecated 废弃，为了兼容旧版 WorkerPoolDispatcher 而保留
     */
    public function start()
    {
        $this->run();
    }

    /**
     * 启动
     */
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
                $this->handle($data);
            }
        });
        Coroutine::create(function () {
            $this->quit->pop();
            $this->jobChannel->close();
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
