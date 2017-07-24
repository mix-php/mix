<?php

/**
 * Request类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\console;

use express\base\Object;

class Request extends Object
{

    // 请求数据
    private $param;
    private $route;

    // 初始化
    public function init()
    {
        // 解析参数
        $param = [];
        foreach($GLOBALS['argv'] as $key =>$value){
            if($key > 1){
                $param[] = $value;
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

    // 设置Route值
    public function setRoute($route)
    {
        $this->route = $route;
    }

    // 获取GET值
    public function param($name = null)
    {
        return is_null($name) ? $this->param : $this->param[$name];
    }

    // 获取Route值
    public function route($name = null)
    {
        return is_null($name) ? $this->route : $this->route[$name];
    }

}
