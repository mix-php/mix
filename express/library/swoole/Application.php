<?php

/**
 * App类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\swoole;

use express\base\Application;

class Application extends Application
{

    /**
     * 执行功能 (LNSMP架构)
     */
    public function run($requester, $responder)
    {
        $request  = \Express::$app->request->setRequester($requester);
        $response = \Express::$app->response->setResponder($responder);
        $method   = strtoupper($requester->header['request_method']);
        $action   = empty($requester->header['pathinfo']) ? '' : substr($requester->header['pathinfo'], 1);
        $content  = $this->runAction($method, $action, ['request' => $request, 'response' => $response]);
        $response->setContent($content)->send();
    }

}
