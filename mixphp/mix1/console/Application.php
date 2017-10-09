<?php

namespace mix\console;

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 */
class Application extends \mix\base\Application
{

    /**
     * 执行功能 (CLI模式)
     */
    public function run()
    {
        if (PHP_SAPI != 'cli') {
            die('请在 CLI 模式下运行' . PHP_EOL);
        }
        $method  = 'CLI';
        $action  = empty($GLOBALS['argv'][1]) ? '' : $GLOBALS['argv'][1];
        $content = $this->runAction($method, $action);
        \Mix::app()->response->setContent($content)->send();
        $this->cleanComponent();
    }

    /**
     * 执行一个外部程序
     */
    public function exec($command)
    {
        exec($command, $output, $returnVar);
        if ($returnVar != 0) {
            throw new \mix\exception\CommandException('命令执行错误');
        }
        return $output;
    }

}
