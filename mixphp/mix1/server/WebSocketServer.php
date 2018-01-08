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

    // onRequest 回调配置
    public $onRequest = [];

    // onMessage 回调配置
    public $onMessage = [];

    // Server对象
    protected $_server;

    // 连接事件回调函数
    protected $_onOpenCallback;

    // 接收消息事件回调函数
    protected $_onMessageCallback;

    // 关闭连接事件回调函数
    protected $_onCloseCallback;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 实例化服务器
        $this->_server = new \Swoole\WebSocket\Server($this->host, $this->port);
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
            try {
                // 进程命名
                if ($workerId < $server->setting['worker_num']) {
                    swoole_set_process_name("mix-websocketd: worker #{$workerId}");
                } else {
                    swoole_set_process_name("mix-websocketd: task #{$workerId}");
                }
            } catch (\Exception $e) {
                \Mix::app()->error->appException($e);
            }
        });
    }

    // 请求事件
    protected function onRequest()
    {
        $this->_server->on('request', function ($request, $response) {
            try {
                // 设置请求响应器
                \Mix::app('webSocket')->request->setRequester($request);
                \Mix::app('webSocket')->response->setResponder($response);
                // 路由处理
                $server = \Mix::app('webSocket')->request->server();
                $method = strtoupper($server['request_method']);
                $action = empty($server['path_info']) ? '' : substr($server['path_info'], 1);
                $action = "{$method} {$action}";
                list($action, $queryParams) = \Mix::app('webSocket')->route->match($action);
                if ($action) {
                    // 路由参数导入请求类
                    \Mix::app('webSocket')->request->setRoute($queryParams);
                    // index处理
                    if (isset($queryParams['controller']) && strpos($action, ':action') !== false) {
                        $action = str_replace(':action', 'index', $action);
                    }
                    // 实例化控制器
                    $action    = "{$this->onRequest['controllerNamespace']}\\{$action}";
                    $classFull = \mix\base\Route::dirname($action);
                    $classPath = \mix\base\Route::dirname($classFull);
                    $className = \mix\base\Route::snakeToCamel(\mix\base\Route::basename($classFull), true);
                    $method    = \mix\base\Route::snakeToCamel(\mix\base\Route::basename($action), true);
                    $class     = "{$classPath}\\{$className}Controller";
                    $method    = "action{$method}";
                    try {
                        $reflect = new \ReflectionClass($class);
                    } catch (\ReflectionException $e) {
                        throw new \mix\exception\NotFoundException('Not Found');
                    }
                    $controller = $reflect->newInstanceArgs();
                    // 判断方法是否存在
                    if (method_exists($controller, $method)) {
                        // 执行控制器的方法
                        $content = $controller->$method($this->_server);
                        // 响应
                        \Mix::app('webSocket')->response->format = \mix\swoole\Response::FORMAT_JSON;
                        \Mix::app('webSocket')->response->setContent($content);
                        \Mix::app('webSocket')->response->send();
                    }
                }
                throw new \mix\exception\NotFoundException('Not Found');
            } catch (\Exception $e) {
                if ($e instanceof \mix\exception\NotFoundException) {
                    \Mix::app('webSocket')->response->format = \mix\swoole\Response::FORMAT_JSON;
                    \Mix::app('webSocket')->response->setContent($this->onRequest['notFound']);
                    \Mix::app('webSocket')->response->send();
                } else {
                    \Mix::app()->error->appException($e);
                }
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
                    \Mix::app('webSocket')->request->setRequester($request);
                    // 执行绑定的回调函数
                    list($object, $method) = $this->_onOpenCallback;
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
                    // 执行绑定的回调函数
                    list($object, $method) = $this->_onCloseCallback;
                    $object->$method($server, $fd);
                } catch (\Exception $e) {
                    \Mix::app()->error->appException($e);
                }
            });
        }
    }

}
