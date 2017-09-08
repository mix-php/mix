<?php

/**
 * App类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\console;

class Application extends \mix\base\Application
{

    /**
     * 执行功能 (CLI模式)
     */
    public function run()
    {
        if(PHP_SAPI != 'cli'){
            die('Please run in cli mode');
        }
        $method  = 'CLI';
        $action  = empty($GLOBALS['argv'][1]) ? '' : $GLOBALS['argv'][1];
        $content = $this->runAction($method, $action);
        \Mix::app()->response->setContent($content)->send();
    }

}
