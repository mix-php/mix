<?php

namespace task\daemon\command;

use mix\console\QueueController;

/**
 * 默认控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController extends QueueController
{

    // 消费者数量
    public $consumerNumber = 3;
    // 服务名称
    public $serverName = 'task';

    public function actionStart()
    {
        $this->daemon();
        $this->start();
    }

    public function onProducerStart(\mix\swoole\QueueProcess $worker)
    {
        // 连接Redis等
        while (true) {
            $worker->checkMaster();
            $worker->push(['hahaha', 1, true]);
            sleep(10);
        }
    }

    public function onConsumerStart(\mix\swoole\QueueProcess $worker, $index)
    {
        // 连接数据库
        while (true) {
            $worker->checkMaster();
            $msg = $worker->pop();
            if (!empty($msg)) {
                var_dump($msg);
            }
        }
    }

}
