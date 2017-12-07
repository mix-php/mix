<?php

namespace mix\swoole;

use \mix\base\Object;

/**
 * 队列服务器类
 * @author 刘健 <coder.liu@qq.com>
 */
class QueueServer extends Object
{

    // 消费者进程数
    public $consumerProcess = 3;
    // 服务名称
    public $serverName = 'task';
    // 主进程pid
    protected $mpid = 0;
    // 工作进程pid集合
    protected $workers = [];
    // 生产者启动事件回调函数
    protected $onProducerStart;
    // 消费者启动事件回调函数
    protected $onConsumerStart;

    // 启动
    public function start()
    {
        \swoole_set_process_name(sprintf("mix-queued: {$this->serverName} %s", 'master'));
        $this->mpid = posix_getpid();
        $this->createProducer();
        $this->createConsumers();
        $this->subProcessWait();
    }

    // 注册Server的事件回调函数
    public function on($event, $callback)
    {
        switch ($event) {
            case 'ProducerStart':
                $this->onProducerStart = $callback;
                break;
            case 'ConsumerStart':
                $this->onConsumerStart = $callback;
                break;
        }
    }

    // 创建生产者
    protected function createProducer()
    {
        $process = new QueueProcess(function ($worker) {
            \swoole_set_process_name(sprintf("mix-queued: {$this->serverName} %s", 'producer'));
            list($object, $method) = $this->onProducerStart;
            $object->$method($worker);
        }, false, false);
        $process->useQueue(ftok(__FILE__, 1), 2);
        $process->mpid    = $this->mpid;
        $pid              = $process->start();
        $this->workers[0] = $pid;
        return $pid;
    }

    // 创建全部消费者
    protected function createConsumers()
    {
        for ($i = 1; $i < $this->consumerProcess; $i++) {
            $this->createConsumer($i);
        }
    }

    // 创建单个消费者
    protected function createConsumer($index)
    {
        $process = new QueueProcess(function ($worker) use ($index) {
            \swoole_set_process_name(sprintf("mix-queued: {$this->serverName} consumer #%s", $index - 1));
            list($object, $method) = $this->onConsumerStart;
            $object->$method($worker, $index - 1);
        }, false, false);
        $process->useQueue(ftok(__FILE__, 1), 2);
        $process->mpid         = $this->mpid;
        $pid                   = $process->start();
        $this->workers[$index] = $pid;
        return $pid;
    }

    // 重启进程
    protected function rebootProcess($ret)
    {
        $pid   = $ret['pid'];
        $index = array_search($pid, $this->workers);
        if ($index !== false) {
            $index = intval($index);
            if ($index == 0) {
                $this->createProducer();
            } else {
                $this->createConsumer($index);
            }
            return;
        }
        throw new \Exception('rebootProcess Error: no pid');
    }

    // 回收结束运行的子进程
    protected function subProcessWait()
    {
        while (true) {
            $ret = \swoole_process::wait();
            if ($ret) {
                $this->rebootProcess($ret);
            }
        }
    }

}
