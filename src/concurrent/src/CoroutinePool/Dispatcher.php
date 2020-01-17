<?php

namespace Mix\Concurrent\CoroutinePool;

use Mix\Bean\BeanInjector;
use Swoole\Coroutine\Channel;
use Mix\Concurrent\Exception\TypeException;
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
    public $workerPool;

    /**
     * 工作者集合
     * @var array
     */
    public $workers = [];

    /**
     * 退出
     * @var Channel
     */
    protected $_quit;

    /**
     * Dispatcher constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config)
    {
        BeanInjector::inject($this, $config);
        $this->init();
    }

    /**
     * 初始化
     */
    public function init()
    {
        if (!isset($this->workerPool)) {
            $this->workerPool = new Channel($this->maxWorkers);
        }
        $this->_quit = new Channel();
    }

    /**
     * 启动
     */
    public function start($worker)
    {
        if (!is_subclass_of($worker, WorkerInterface::class)) {
            throw new TypeException("{$worker} type is not '" . WorkerInterface::class . "'");
        }
        for ($i = 0; $i < $this->maxWorkers; $i++) {
            /** @var AbstractWorker $worker */
            $worker          = new $worker([
                'workerPool' => $this->workerPool,
            ]);
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
            $this->_quit->pop();
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
            $this->_quit->push(true);
        });
    }

}
