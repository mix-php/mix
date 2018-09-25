<?php

namespace apps\httpd\controllers;

use mix\client\PDOCoroutine;
use mix\http\Controller;
use Swoole\Coroutine\Channel;

/**
 * 协程范例
 * @author 刘健 <coder.liu@qq.com>
 */
class CoroutineController extends Controller
{

    // 默认动作
    public function actionIndex()
    {
        // 输出 time: 2，说明是并行执行
        $time = $this->execute()->pop();
        return 'Hello, World! Time: ' . $time;
    }

    // 执行
    public function execute()
    {
        // 并行查询数据
        $chan = new Channel();
        tgo(function () use ($chan) {
            $time = time();
            // 查询数据
            $foo = $this->foo()->pop();
            $bar = $this->bar()->pop();
            // 结果写入通道
            $chan->push(time() - $time);
        });
        return $chan;
    }

    // 查询数据
    public function foo()
    {
        $chan = new Channel();
        tgo(function () use ($chan) {
            // 子协程内只可使用局部变量，因组件为全局变量是不可在子协程内使用的，会导致内存溢出
            $pdo    = PDOCoroutine::newInstanceByConfig('components.[coroutine.pdo]');
            $result = $pdo->createCommand('select sleep(2)')->queryAll();
            $chan->push($result);
        });
        return $chan;
    }

    // 查询数据
    public function bar()
    {
        $chan = new Channel();
        tgo(function () use ($chan) {
            // 子协程内只可使用局部变量，因组件为全局变量是不可在子协程内使用的，会导致内存溢出
            $pdo    = PDOCoroutine::newInstanceByConfig('components.[coroutine.pdo]');
            $result = $pdo->createCommand('select sleep(1)')->queryAll();
            $chan->push($result);
        });
        return $chan;
    }

}
