<?php

/**
 * Controller类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

use mix\base\Object;
use mix\web\View;

class Controller extends Object
{

    // 默认布局
    protected $layout = 'main';

    // 提取视图 (包含布局)
    public function render($name, $data = [])
    {
        if (strpos($name, '.') === false) {
            $name = $this->viewPrefix() . '.' . $name;
        }
        $view            = new View();
        $data['content'] = $view->import($name, $data);
        return $view->import("layout.{$this->layout}", $data);
    }

    // 提取视图 (不包含布局)
    public function renderPartial($name, $data = [])
    {
        if (strpos($name, '.') === false) {
            $name = $this->viewPrefix() . '.' . $name;
        }
        $view = new View();
        return $view->import($name, $data);
    }

    // 视图前缀
    private function viewPrefix()
    {
        return \mix\base\Route::camelToSnake(str_replace([\Mix::$app->controllerNamespace . '\\', '\\', 'Controller'], ['', '.', ''], get_class($this)));
    }

}
