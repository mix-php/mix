<?php

/**
 * App类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

class Application extends \mix\base\Application
{

    /**
     * 执行功能 (LAMP|LNMP架构)
     */
    public function run()
    {
        \Mix::$app->error->register(\Mix::$app->response);
        $method  = empty($_SERVER['REQUEST_METHOD']) ? (PHP_SAPI == 'cli' ? 'CLI' : '') : $_SERVER['REQUEST_METHOD'];
        $action  = empty($_SERVER['PATH_INFO']) ? '' : substr($_SERVER['PATH_INFO'], 1);
        $content = $this->runAction([$method, $action]);
        \Mix::$app->response->setContent($content)->send();
    }

}
