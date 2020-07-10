<?php

namespace Mix\WorkerPool;

use Swoole\Coroutine\Channel;
use Mix\Coroutine\Coroutine;

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
    public $workerPool;

    /**
     * 任务通道
     * @var Channel
     */
    public $jobChannel;

    /**
     * 退出
     * @var Channel
     */
    protected $quit;

    /**
     * AbstractWorker constructor.
     * @param Channel $workerPool
     */
    public function __construct(Channel $workerPool)
    {
        $this->workerPool = $workerPool;
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
     */
    public function start()
    {
        Coroutine::create(function () {
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
