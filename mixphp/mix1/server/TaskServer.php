<?php

namespace mix\server;

use mix\base\BaseObject;
use mix\process\TaskProcess;

/**
 * 任务服务器类
 * @author 刘健 <coder.liu@qq.com>
 */
class TaskServer extends BaseObject
{

    // 左进程数
    public $leftProcess = 1;

    // 右进程数
    public $rightProcess = 3;

    // 服务名称
    public $name = '';

    // 主进程pid
    protected $mpid = 0;

    // 工作进程pid集合
    protected $workers = [];

    // 左进程启动事件回调函数
    protected $onLeftStart;

    // 右进程启动事件回调函数
    protected $onRightStart;

    // 启动
    public function start()
    {
        swoole_set_process_name(sprintf("mix-taskd: {$this->name} %s", 'master'));
        $this->mpid = posix_getpid();
        $this->createLeftProcesses();
        $this->createRightProcesses();
        $this->subProcessWait();
    }

    // 注册Server的事件回调函数
    public function on($event, $callback)
    {
        switch ($event) {
            case 'LeftStart':
                $this->onLeftStart = $callback;
                break;
            case 'RightStart':
                $this->onRightStart = $callback;
                break;
        }
    }

    // 创建全部左进程
    protected function createLeftProcesses()
    {
        for ($i = 0; $i < $this->leftProcess; $i++) {
            $this->createProcess($i, $this->onLeftStart, 'left');
        }
    }

    // 创建全部右进程
    protected function createRightProcesses()
    {
        for ($i = 0; $i < $this->rightProcess; $i++) {
            $this->createProcess($i, $this->onRightStart, 'right');
        }
    }

    // 创建进程
    protected function createProcess($index, $callback, $processType)
    {
        if (!isset($callback)) {
            throw new \Exception('Create Process Error: ' . ($processType == 'left' ? '[LeftStart]' : '[RightStart]') . ' no callback.');
        }
        $process = new TaskProcess(function ($worker) use ($index, $callback, $processType) {
            swoole_set_process_name(sprintf("mix-taskd: {$this->name} {$processType} #%s", $index));
            list($object, $method) = $callback;
            $object->$method($worker, $index);
        }, false, false);
        $process->useQueue(ftok(__FILE__, 1), 2);
        $process->mpid       = $this->mpid;
        $pid                 = $process->start();
        $this->workers[$pid] = [$index, $callback, $processType];
        return $pid;
    }

    // 重启进程
    protected function rebootProcess($ret)
    {
        $pid = $ret['pid'];
        if (isset($this->workers[$pid])) {
            list($index, $callback, $processType) = $this->workers[$pid];
            $this->createProcess($index, $callback, $processType);
            return;
        }
        throw new \Exception('Reboot Process Error: no pid.');
    }

    // 回收结束运行的子进程
    protected function subProcessWait()
    {
        while (true) {
            $ret = \Swoole\Process::wait();
            if ($ret) {
                $this->rebootProcess($ret);
            }
        }
    }

}
