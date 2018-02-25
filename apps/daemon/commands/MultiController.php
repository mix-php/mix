<?php

namespace apps\daemon\commands;

use mix\console\Controller;

/**
 * 这是一个多进程守护进程的范例
 * 进程模型为：生产者消费者模型
 * 你可以自由选择是左进程当生产者还是右进程当生产者，本范例是左进程当生产者
 * @author 刘健 <coder.liu@qq.com>
 */
class MultiController extends Controller
{

    // 是否后台运行
    protected $d = false;

    /**
     * 获取服务
     * @return \mix\server\TaskServer
     */
    public function getServer()
    {
        return \Mix::app()->createObject('taskServer');
    }

    // 启动守护进程
    public function actionStart()
    {
        // 蜕变为守护进程
        if ($this->d) {
            self::daemon();
        }
        // 启动服务
        $server       = $this->getServer();
        $server->name = $this->getControllerName();
        $server->on('LeftStart', [$this, 'onLeftStart']);
        $server->on('RightStart', [$this, 'onRightStart']);
        $server->start();
    }

    // 左进程启动事件回调函数
    public function onLeftStart(\mix\process\TaskProcess $worker)
    {
        // 模型内使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        $queueModel = new \apps\common\models\QueueModel();
        // 循环执行任务
        for ($j = 0; $j < 16000; $j++) {
            $worker->checkMaster();
            // 从队列取出一条消息
            $msg = $queueModel->pop();
            // 将消息推送给消费者进程处理
            $worker->push($msg);
        }
    }

    // 右进程启动事件回调函数
    public function onRightStart(\mix\process\TaskProcess $worker, $index)
    {
        // 循环执行任务
        for ($j = 0; $j < 16000; $j++) {
            $worker->checkMaster();
            // 从队列中抢占一条消息
            $msg = $worker->pop();
            if (!empty($msg)) {
                // 处理消息，比如：发送短信、发送邮件、微信推送
                // ...
            }
        }
    }

}
