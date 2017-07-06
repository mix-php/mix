<?php

/**
 * Controller类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\web;

use express\base\Object;
use express\web\View;

class Controller extends Object
{

    // 默认布局
    protected $layout = 'main';

    // 提取视图 (包含布局)
    public function render($name, $data = [])
    {
        if (strpos($name, '.') === false) {
            $name = $this->controllerName() . '.' . $name;
        }
        $view            = new View();
        $data['content'] = $view->import($name, $data);
        return $view->import("layout.{$this->layout}", $data);
    }

    // 提取视图 (不包含布局)
    public function renderPartial($name, $data = [])
    {
        if (strpos($name, '.') === false) {
            $name = $this->controllerName() . '.' . $name;
        }
        $view = new View();
        return $view->import($name, $data);
    }

    // 控制器名称
    public function controllerName()
    {
        $className = basename(get_class($this));
        return substr($className, 0, strpos($className, 'Controller'));
    }

}
