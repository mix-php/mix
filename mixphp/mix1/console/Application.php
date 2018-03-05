<?php

namespace mix\console;

use mix\base\Component;

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 */
class Application extends \mix\base\Application
{

    // NotFound错误消息
    protected $_notFoundMessage = 'Command Not Found';

    // 执行功能 (CLI模式)
    public function run()
    {
        if (PHP_SAPI != 'cli') {
            throw new \RuntimeException('请在 CLI 模式下运行');
        }
        \mix\console\Error::register();
        $method               = 'CLI';
        $action               = empty($GLOBALS['argv'][1]) ? '' : $GLOBALS['argv'][1];
        $controllerAttributes = \Mix::app()->request->param();
        \Mix::app()->response->content = $this->runAction($method, $action, $controllerAttributes);
        \Mix::app()->response->send();
    }

    // 获取组件
    public function __get($name)
    {
        if (!is_null($this->_componentNamespace)) {
            $name = "{$this->_componentNamespace}.{$name}";
        }
        // 返回单例
        if (isset($this->_components[$name])) {
            // 返回对象
            return $this->_components[$name];
        }
        // 装载组件
        $this->loadComponent($name);
        // 返回对象
        return $this->_components[$name];
    }

}
