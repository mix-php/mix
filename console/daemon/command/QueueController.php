<?php

namespace console\daemon\command;

use mix\console\Controller;
use mix\process\QueueProcess;

/**
 * 队列服务控制器
 * 这是一个多进程守护进程的范例，进程模型为：生产者消费者模型
 * @author 刘健 <coder.liu@qq.com>
 */
class QueueController extends Controller
{

    // 启动服务
    public function actionStart()
    {
        // 脱离终端
        $this->daemon();
        // 启动服务
        $server       = \Mix::createObject(\Mix::app()->objects['server']);
        $server->name = $this->getControllerName();
        $server->on('LeftStart', [$this, 'onLeftStart']);
        $server->on('RightStart', [$this, 'onRightStart']);
        $server->start();
    }

    // 左进程启动事件回调函数
    public function onLeftStart(QueueProcess $worker)
    {
        // 连接redis等，使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        // ...
        // 循环执行任务
        while (true) {
            $worker->checkMaster();
            // 从队列取出一条消息
            $msg = \Mix::app()->redis->brpop('queue_key');
            // 将消息推送给消费者进程处理
            $worker->push($msg);
        }
    }

    // 右进程启动事件回调函数
    public function onRightStart(QueueProcess $worker, $index)
    {
        // 连接数据库，使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        // ...
        // 循环执行任务
        while (true) {
            $worker->checkMaster();
            // 从队列中抢占一条消息
            $msg = $worker->pop();
            if (!empty($msg)) {
                // 处理消息
                var_dump($msg);
            }
        }
    }

}
