<?php

namespace Mix\Concurrent\CoroutinePool;

/**
 * Interface WorkerInterface
 * @package Mix\Concurrent\CoroutinePool
 * @author liu,jian <coder.keda@gmail.com>
 */
interface WorkerInterface
{

    /**
     * 启动
     */
    public function start();

    /**
     * 停止
     */
    public function stop();

    /**
     * 处理
     * @param $data
     */
    public function handle($data);

}
