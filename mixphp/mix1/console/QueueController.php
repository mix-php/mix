<?php

namespace mix\console;

use mix\swoole\QueueServer;
use \mix\swoole\QueueProcess;

/**
 * QueueController类
 * @author 刘健 <coder.liu@qq.com>
 */
class QueueController extends Controller
{

    // 消费者进程数
    public $consumerProcess = 3;
    // 服务名称
    public $serverName;

    // 启动服务
    public function start()
    {
        if (!isset($this->serverName)) {
            $class            = str_replace('Controller', '', get_class($this));
            $this->serverName = \mix\base\Route::camelToSnake(\mix\base\Route::basename($class), '-');
        }
        $queueServer = new QueueServer([
            'consumerProcess' => $this->consumerProcess,
            'serverName'     => $this->serverName,
        ]);
        $queueServer->on('ProducerStart', [$this, 'onProducerStart']);
        $queueServer->on('ConsumerStart', [$this, 'onConsumerStart']);
        $queueServer->start();
    }

    // 生产者启动事件
    public function onProducerStart(QueueProcess $worker)
    {
    }

    // 消费者启动事件
    public function onConsumerStart(QueueProcess $worker, $index)
    {
    }

}
