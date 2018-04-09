<?php

namespace mix\console;

use mix\base\Component;

/**
 * Request组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Request extends Component
{

    // ROUTE参数
    protected $_route = [];

    // CLI参数
    protected $_param = [];

    // 初始化
    public function onInitialize()
    {
        parent::onInitialize();
        $this->setParam();
    }

    // 设置CLI参数
    protected function setParam()
    {
        // 解析参数
        $param = [];
        foreach ($GLOBALS['argv'] as $key => $value) {
            if ($key > 1) {
                if (substr($value, 0, 2) == '--') {
                    $param[] = substr($value, 2);
                } else if (substr($value, 0, 1) == '-') {
                    $param[] = substr($value, 1);
                }
            }
        }
        parse_str(implode('&', $param), $param);
        // 设置默认值
        foreach ($param as $name => $value) {
            if ($value === '') {
                $param[$name] = true;
            }
        }
        $this->_param = $param;
    }

    // 设置ROUTE值
    public function setRoute($route)
    {
        $this->_route = $route;
    }

    // 获取CLI参数
    public function param($name = null)
    {
        return self::fetch($name, $this->_param);
    }

    // 获取ROUTE值
    public function route($name = null)
    {
        return self::fetch($name, $this->_route);
    }

    // 提取数据
    protected static function fetch($name, $container)
    {
        return is_null($name) ? $container : (isset($container[$name]) ? $container[$name] : null);
    }

}
