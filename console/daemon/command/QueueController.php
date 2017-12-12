<?php

namespace console\daemon\command;

use mix\console\Controller;
use mix\process\QueueProcess;

/**
 * 队列服务控制器
 * 这是一个多进程守护进程的范例，进程模型为：生产者消费者模型
 * 你可以自由选择是左进程当生产者还是右进程当生产者，本范例是左进程当生产者
 * @author 刘健 <coder.liu@qq.com>
 */
class QueueController extends Controller
{

    // 启动服务
    public function actionStart()
    {
        // 蜕变为守护进程
        self::daemon();
        // 启动服务
        $server       = \Mix::app()->createObject('server');
        $server->name = $this->getControllerName();
        $server->on('LeftStart', [$this, 'onLeftStart']);
        $server->on('RightStart', [$this, 'onRightStart']);
        $server->start();
    }

    // 左进程启动事件回调函数
    public function onLeftStart(QueueProcess $worker)
    {
        // 模型内使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        $queueModel = new \console\common\model\QueueModel();
        // 循环执行任务
        while (true) {
            $worker->checkMaster();
            // 从队列取出一条消息
            $msg = $queueModel->pop();
            // 将消息推送给消费者进程处理
            $worker->push($msg);
        }
    }

    // 右进程启动事件回调函数
    public function onRightStart(QueueProcess $worker, $index)
    {
        // 模型内使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        $tableModel = new \console\common\model\TableModel();
        // 循环执行任务
        while (true) {
            $worker->checkMaster();
            // 从队列中抢占一条消息
            $msg = $worker->pop();
            if (!empty($msg)) {
                // 处理消息
                // ...
                // 处理结果入库
                $tableModel->update($msg);
            }
        }
    }

}
