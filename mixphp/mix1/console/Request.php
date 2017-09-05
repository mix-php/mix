<?php

/**
 * Request类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\console;

use mix\base\Object;

class Request extends Object
{

    // CLI参数
    private $param = [];

    // ROUTE参数
    private $route = [];

    // 初始化
    public function init()
    {
        // 解析参数
        $param = [];
        foreach ($GLOBALS['argv'] as $key => $value) {
            if ($key > 1 && substr($value, 0, 2) == '--') {
                $param[] = substr($value, 2);
            }
        }
        parse_str(implode('&', $param), $param);
        // 赋值
        $this->setParam($param);
    }

    // 设置GET值
    public function setParam($param)
    {
        $this->param = $param;
    }

    // 设置ROUTE值
    public function setRoute($route)
    {
        $this->route = $route;
    }

    // 获取CLI参数
    public function param($name = null)
    {
        return is_null($name) ? $this->param : (isset($this->param[$name]) ? $this->param[$name] : null);
    }

    // 获取ROUTE值
    public function route($name = null)
    {
        return is_null($name) ? $this->route : (isset($this->route[$name]) ? $this->route[$name] : null);
    }

}
