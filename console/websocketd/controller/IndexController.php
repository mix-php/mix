<?php

namespace console\websocketd\controller;

/**
 * 控制器
 * @author 刘健 <coder.liu@qq.com>
 */
class IndexController
{

    public function actionIndex(\Swoole\WebSocket\Server $webSocket, \mix\swoole\Request $request, \mix\swoole\Response $response)
    {
        foreach ($webSocket->table as $fd => $item) {
            $webSocket->push($fd, 'message');
        }
        $response->format = \mix\swoole\Response::FORMAT_JSON;
        $response->setContent(['errcode' => 0, 'errmsg' => 'ok']);
        $response->send();
    }

}
