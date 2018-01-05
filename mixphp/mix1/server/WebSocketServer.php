<?php

namespace mix\server;

use mix\base\BaseObject;

/**
 * Http服务器类
 * @author 刘健 <coder.liu@qq.com>
 */
class WebSocketServer extends BaseObject
{

    // 主机
    public $host;

    // 主机
    public $port;

    // 运行时的各项参数
    public $setting = [];

    // Server对象
    protected $server;

    // Worker进程启动事件回调函数
    protected $onWorkerStart;

    // HTTP请求事件回调函数
    protected $onRequest;

    // 连接事件回调函数
    protected $onOpen;

    // 接收消息事件回调函数
    protected $onMessage;

    // 关闭连接事件回调函数
    protected $onClose;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 实例化服务器
        $this->server = new \Swoole\WebSocket\Server($this->host, $this->port);
        // 新建日志目录
        if (isset($this->setting['log_file'])) {
            $dir = dirname($this->setting['log_file']);
            is_dir($dir) or mkdir($dir);
        }
    }

    // 启动服务
    public function start()
    {
        $this->onStart();
        $this->onManagerStart();
        $this->onWorkerStart();
        $this->onRequest();
        $this->onOpen();
        $this->onMessage();
        $this->onClose();
        $this->server->set($this->setting);
        $this->server->start();
    }

    // 添加属性至服务
    public function setServerAttribute($key, $object)
    {
        $this->server->$key = $object;
    }

    // 注册Server的事件回调函数
    public function on($event, $callback)
    {
        switch ($event) {
            case 'WorkerStart':
                $this->onWorkerStart = $callback;
                break;
            case 'Request':
                $this->onRequest = $callback;
                break;
            case 'Open':
                $this->onOpen = $callback;
                break;
            case 'Message':
                $this->onMessage = $callback;
                break;
            case 'Close':
                $this->onClose = $callback;
                break;
        }
    }

    // 主进程启动事件
    protected function onStart()
    {
        $this->server->on('Start', function ($server) {
            // 进程命名
            swoole_set_process_name("mix-websocketd: master {$this->host}:{$this->port}");
        });
    }

    // 管理进程启动事件
    protected function onManagerStart()
    {
        $this->server->on('ManagerStart', function ($server) {
            // 进程命名
            swoole_set_process_name("mix-websocketd: manager");
        });
    }

    // 工作进程启动事件
    protected function onWorkerStart()
    {
        $this->server->on('WorkerStart', function ($server, $workerId) {
            try {
                // 进程命名
                if ($workerId < $server->setting['worker_num']) {
                    swoole_set_process_name("mix-websocketd: worker #{$workerId}");
                } else {
                    swoole_set_process_name("mix-websocketd: task #{$workerId}");
                }
                // 执行绑定的回调函数
                if (isset($this->onWorkerStart)) {
                    list($object, $method) = $this->onWorkerStart;
                    $object->$method($server, $workerId);
                }
            } catch (\Exception $e) {
                \Mix::app()->error->appException($e);
            }
        });
    }

    // 请求事件
    protected function onRequest()
    {
        if (isset($this->onRequest)) {
            $this->server->on('request', function ($request, $response) {
                try {
                    // 组件初始化处理
                    \Mix::app('webSocket')->request->setRequester($request);
                    \Mix::app('webSocket')->response->setResponder($response);
                    // 执行绑定的回调函数
                    list($object, $method) = $this->onRequest;
                    $object->$method($this->server);
                } catch (\Exception $e) {
                    \Mix::app()->error->appException($e);
                }
            });
        }
    }

    // 客户端与服务器建立连接并完成握手后会回调此函数
    protected function onOpen()
    {
        if (isset($this->onOpen)) {
            $this->server->on('open', function ($server, $request) {
                try {
                    // 组件初始化处理
                    \Mix::app('webSocket')->request->setRequester($request);
                    // 执行绑定的回调函数
                    list($object, $method) = $this->onOpen;
                    $object->$method($server, $request->fd);
                } catch (\Exception $e) {
                    \Mix::app()->error->appException($e);
                }
            });
        }
    }

    // 当服务器收到来自客户端的数据帧时会回调此函数
    protected function onMessage()
    {
        if (isset($this->onMessage)) {
            $this->server->on('message', function ($server, $frame) {
                try {
                    // 执行绑定的回调函数
                    list($object, $method) = $this->onMessage;
                    $object->$method($server, $frame);
                } catch (\Exception $e) {
                    \Mix::app()->error->appException($e);
                }
            });
        }
    }

    // 客户端与服务器关闭连接后会回调此函数
    protected function onClose()
    {
        if (isset($this->onClose)) {
            $this->server->on('close', function ($server, $fd) {
                try {
                    // 执行绑定的回调函数
                    list($object, $method) = $this->onClose;
                    $object->$method($server, $fd);
                } catch (\Exception $e) {
                    \Mix::app()->error->appException($e);
                }
            });
        }
    }

}
