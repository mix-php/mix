<?php

namespace mix\websocket;

use mix\base\Component;

/**
 * SessionReader组件
 * @author 刘健 <coder.liu@qq.com>
 */
class MessageHandler extends Component
{

    // 控制器命名空间
    public $controllerNamespace = '';

    // 路由规则
    public $rules = [];

    // 服务
    protected $_server;

    // 文件描述符
    protected $_fd;

    // NotFound错误消息
    protected $_notFoundMessage = 'MessageHandler: Action Not Found';

    // 设置服务
    public function setServer($server)
    {
        $this->_server = $server;
        return $this;
    }

    // 设置文件描述符
    public function setFd($fd)
    {
        $this->_fd = $fd;
        return $this;
    }

    // 执行功能
    public function runAction($action, $paramArray = [])
    {
        isset($this->rules[$action]) and $rule = $this->rules[$action];
        // 匹配成功
        if (isset($rule)) {
            // 实例化控制器
            $rule      = "{$this->controllerNamespace}\\{$rule}";
            $classFull = \mix\base\Route::dirname($rule);
            $classPath = \mix\base\Route::dirname($classFull);
            $className = \mix\base\Route::snakeToCamel(\mix\base\Route::basename($classFull), true);
            $method    = \mix\base\Route::snakeToCamel(\mix\base\Route::basename($rule), true);
            $class     = "{$classPath}\\{$className}Controller";
            $method    = "action{$method}";
            try {
                $reflect = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                $this->clean();
                throw new \mix\exception\NotFoundException($this->_notFoundMessage);
            }
            $controller = $reflect->newInstanceArgs([[
                '_server' => $this->_server,
                '_fd'     => $this->_fd,
            ]]);
            // 判断方法是否存在
            if (method_exists($controller, $method)) {
                // 执行控制器的方法
                $content = call_user_func_array([$controller, $method], $paramArray);
                // 响应
                if (!is_null($content)) {
                    $this->_server->push($this->_fd, $content);
                }
                // 清扫
                $this->clean();
                return;
            }
        }
        $this->clean();
        throw new \mix\exception\NotFoundException($this->_notFoundMessage);
    }

    // 清扫
    public function clean()
    {
        $this->setServer(null);
        $this->setFd(null);
    }

}
