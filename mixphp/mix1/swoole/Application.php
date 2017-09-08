<?php

/**
 * App类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\swoole;

class Application extends \mix\base\Application
{

    /**
     * 执行功能 (Swoole架构)
     */
    public function run($requester, $responder)
    {
        $method = strtoupper($requester->server['request_method']);
        $action = empty($requester->server['path_info']) ? '' : substr($requester->server['path_info'], 1);
        $content = $this->runAction($method, $action);
        \Mix::$app->response->setContent($content)->send();
    }

}
