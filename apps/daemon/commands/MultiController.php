<?php

namespace apps\daemon\commands;

use mix\console\Controller;
use mix\swoole\Process;
use mix\swoole\TaskProcess;
use mix\swoole\TaskServer;

/**
 * 这是一个多进程守护进程的范例
 * 进程模型为：生产者消费者模型
 * 你可以自由选择是左进程当生产者还是右进程当生产者，本范例是左进程当生产者
 * @author 刘健 <coder.liu@qq.com>
 */
class MultiController extends Controller
{

    // PID 文件
    const PID_FILE = '/var/run/multi.pid';

    // 是否后台运行
    protected $d = false;

    /**
     * 获取服务
     * @return TaskServer
     */
    public function getServer()
    {
        return \Mix::createObject(
            [
                // 类路径
                'class'        => 'mix\swoole\TaskServer',
                // 左进程数
                'leftProcess'  => 1,
                // 右进程数
                'rightProcess' => 3,
                // 进程队列的key，int类型
                'queueKey'     => ftok(__FILE__, 1),
            ]
        );
    }

    // 启动
    public function actionStart()
    {
        $controllerName = \Mix::app()->request->route('controller');
        // 重复启动处理
        if ($pid = Process::getMasterPid(self::PID_FILE)) {
            return "mix-daemon '{$controllerName}' is running, PID : {$pid}." . PHP_EOL;
        }
        // 启动提示
        echo "mix-daemon '{$controllerName}' start successed." . PHP_EOL;
        // 蜕变为守护进程
        if ($this->d) {
            Process::daemon();
        }
        // 写入 PID 文件
        Process::writePid(self::PID_FILE);
        // 启动服务
        $server       = $this->getServer();
        $server->name = $controllerName;
        $server->on('LeftStart', [$this, 'onLeftStart']);
        $server->on('RightStart', [$this, 'onRightStart']);
        $server->start();
    }

    // 停止
    public function actionStop()
    {
        $controllerName = \Mix::app()->request->route('controller');
        if ($pid = Process::getMasterPid(self::PID_FILE)) {
            Process::kill($pid);
            while (Process::isRunning($pid)) {
                // 等待进程退出
                usleep(100000);
            }
            return "mix-daemon '{$controllerName}' stop completed." . PHP_EOL;
        } else {
            return "mix-daemon '{$controllerName}' is not running." . PHP_EOL;
        }
    }

    // 重启
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
    }

    // 查看状态
    public function actionStatus()
    {
        $controllerName = \Mix::app()->request->route('controller');
        if ($pid = Process::getMasterPid(self::PID_FILE)) {
            return "mix-daemon '{$controllerName}' is running, PID : {$pid}." . PHP_EOL;
        } else {
            return "mix-daemon '{$controllerName}' is not running." . PHP_EOL;
        }
    }

    // 左进程启动事件回调函数
    public function onLeftStart(TaskProcess $worker, $index)
    {
        // 模型内使用长连接版本的数据库组件，这样组件会自动帮你维护连接不断线
        $queueModel = new \apps\common\models\QueueModel();
        // 循环执行任务
        for ($j = 0; $j < 16000; $j++) {
            $worker->checkMaster(TaskProcess::PRODUCER);
            // 从消息队列中间件取出一条消息
            $msg = $queueModel->pop();
            // 将消息推送给消费者进程去处理
            $worker->push($msg);
        }
    }

    // 右进程启动事件回调函数
    public function onRightStart(TaskProcess $worker, $index)
    {
        // 循环执行任务
        for ($j = 0; $j < 16000; $j++) {
            $worker->checkMaster();
            // 从进程队列中抢占一条消息
            $msg = $worker->pop();
            if (!empty($msg)) {
                // 处理消息，比如：发送短信、发送邮件、微信推送
                // ...
            }
        }
    }

}
