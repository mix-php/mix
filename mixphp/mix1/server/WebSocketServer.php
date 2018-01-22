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

    // 端口
    public $port;

    // 运行时的各项参数
    public $setting = [];

    // Server对象
    protected $_server;

    // 连接事件回调函数
    protected $_onOpenCallback;

    // 接收消息事件回调函数
    protected $_onMessageCallback;

    // 关闭连接事件回调函数
    protected $_onCloseCallback;

    // 给不可访问属性赋值
    public function __set($name, $value)
    {
        $this->_server->$name = $value;
    }

    // 读取不可访问属性的值
    public function __get($name)
    {
        return $this->_server->$name;
    }

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 实例化服务器
        $this->_server = new \Swoole\WebSocket\Server($this->host, $this->port);
    }

    // 启动服务
    public function start()
    {
        $this->onStart();
        $this->onManagerStart();
        $this->onWorkerStart();
        $this->onOpen();
        $this->onMessage();
        $this->onClose();
        $this->_server->set($this->setting);
        $this->_server->start();
    }

    // 添加属性至服务
    public function setServerAttribute($key, $object)
    {
        $this->_server->$key = $object;
    }

    // 注册Server的事件回调函数
    public function on($event, $callback)
    {
        switch ($event) {
            case 'Open':
                $this->_onOpenCallback = $callback;
                break;
            case 'Message':
                $this->_onMessageCallback = $callback;
                break;
            case 'Close':
                $this->_onCloseCallback = $callback;
                break;
        }
    }

    // 主进程启动事件
    protected function onStart()
    {
        $this->_server->on('Start', function ($server) {
            // 进程命名
            swoole_set_process_name("mix-websocketd: master {$this->host}:{$this->port}");
        });
    }

    // 管理进程启动事件
    protected function onManagerStart()
    {
        $this->_server->on('ManagerStart', function ($server) {
            // 进程命名
            swoole_set_process_name("mix-websocketd: manager");
        });
    }

    // 工作进程启动事件
    protected function onWorkerStart()
    {
        $this->_server->on('WorkerStart', function ($server, $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                swoole_set_process_name("mix-websocketd: worker #{$workerId}");
            } else {
                swoole_set_process_name("mix-websocketd: task #{$workerId}");
            }
        });
    }

    // 客户端与服务器建立连接并完成握手后会回调此函数
    protected function onOpen()
    {
        if (isset($this->_onOpenCallback)) {
            $this->_server->on('open', function ($server, $request) {
                try {
                    // 组件初始化处理
                    $swooleRequest = new \mix\swoole\Request();
                    $swooleRequest->setRequester($request);
                    // 执行绑定的回调函数
                    list($object, $method) = $this->_onOpenCallback;
                    $object->$method($server, $request->fd, $swooleRequest);
                } catch (\Exception $e) {
                    \Mix::app()->error->appException($e);
                }
            });
        }
    }

    // 当服务器收到来自客户端的数据帧时会回调此函数
    protected function onMessage()
    {
        if (isset($this->_onMessageCallback)) {
            $this->_server->on('message', function ($server, $frame) {
                try {
                    // 执行绑定的回调函数
                    list($object, $method) = $this->_onMessageCallback;
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
        if (isset($this->_onCloseCallback)) {
            $this->_server->on('close', function ($server, $fd) {
                try {
                    // 判断是否为WebSocket客户端
                    $websocketStatus = $this->_server->connection_info($fd)['websocket_status'];
                    if (in_array($websocketStatus, [WEBSOCKET_STATUS_CONNECTION, WEBSOCKET_STATUS_HANDSHAKE, WEBSOCKET_STATUS_FRAME])) {
                        // 执行绑定的回调函数
                        list($object, $method) = $this->_onCloseCallback;
                        $object->$method($server, $fd);
                    }
                } catch (\Exception $e) {
                    \Mix::app()->error->appException($e);
                }
            });
        }
    }

}
