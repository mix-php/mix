<?php

namespace mix\console;

/**
 * Controller类
 * @author 刘健 <coder.liu@qq.com>
 */
class Controller extends \mix\base\Controller
{

    // 获取控制器名称
    public function getControllerName()
    {
        $class = str_replace('Controller', '', get_class($this));
        return \mix\base\Route::camelToSnake(\mix\base\Route::basename($class), '-');
    }

}
