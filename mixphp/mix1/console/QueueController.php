<?php

namespace mix\console;

use mix\swoole\QueueServer;

/**
 * QueueController类
 * @author 刘健 <coder.liu@qq.com>
 */
class QueueController extends Controller
{

    // 消费者数量
    public $consumerNumber = 3;
    // 服务名称
    public $serverName = 'task';

    // 启动
    public function start()
    {
        $queueServer = new QueueServer([
            'consumerNumber' => $this->consumerNumber,
            'serverName'     => $this->serverName,
        ]);
        $queueServer->on('ProducerStart', [$this, 'onProducerStart']);
        $queueServer->on('ConsumerStart', [$this, 'onConsumerStart']);
        $queueServer->start();
    }

    // 生产者启动事件
    public function onProducerStart(\mix\swoole\QueueProcess $worker)
    {
    }

    // 消费者启动事件
    public function onConsumerStart(\mix\swoole\QueueProcess $worker, $index)
    {
    }

}
