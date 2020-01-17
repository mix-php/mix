<?php

namespace Mix\Concurrent\CoroutinePool;

use Mix\Bean\BeanInjector;
use Swoole\Coroutine\Channel;
use Mix\Concurrent\Coroutine;

/**
 * Class AbstractWorker
 * @package Mix\Concurrent\CoroutinePool
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
    protected $_quit;

    /**
     * AbstractWorker constructor.
     * @param array $config
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
        $this->jobChannel = new Channel();
        $this->_quit      = new Channel();
    }

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
            $this->_quit->pop();
            $this->jobChannel->close();
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
