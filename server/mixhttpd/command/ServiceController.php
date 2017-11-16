<?php

/**
 * 控制器
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mixhttpd\command;

use mix\console\Controller;
use mixhttpd\library\Service;

class ServiceController extends Controller
{

    // 启动服务
    public function actionStart()
    {
        if ($pid = Service::getPid()) {
            return "MixHttpd 正在运行, PID : {$pid}." . PHP_EOL;
        }
        $server = \Mix::app()->server;
        if (!is_null(\Mix::app()->request->param('hot-update'))) {
            $server->setting['max_request'] = 1;
        }
        if (!is_null(\Mix::app()->request->param('foreground'))) {
            $server->setting['daemonize'] = false;
        }
        return $server->start();
    }

    // 停止服务
    public function actionStop()
    {
        if ($pid = Service::getPid()) {
            Service::killMaster($pid);
            while (Service::isRunning($pid)) {
            }
            return 'MixHttpd 停止完成.' . PHP_EOL;
        } else {
            return 'MixHttpd 没有运行.' . PHP_EOL;
        }
    }

    // 重启服务
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
    }

}
