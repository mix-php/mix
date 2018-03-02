<?php

namespace mix\web;

use mix\web\View;

/**
 * Controller类
 * @author 刘健 <coder.liu@qq.com>
 */
class Controller extends \mix\base\Controller
{

    // 默认布局
    protected $layout = 'main';

    // 渲染视图 (包含布局)
    public function render($name, $data = [])
    {
        if (strpos($name, '.') === false) {
            $name = $this->viewPrefix() . '.' . $name;
        }
        $view            = new View();
        $data['content'] = $view->render($name, $data);
        return $view->render("layouts.{$this->layout}", $data);
    }

    // 渲染视图 (不包含布局)
    public function renderPartial($name, $data = [])
    {
        if (strpos($name, '.') === false) {
            $name = $this->viewPrefix() . '.' . $name;
        }
        $view = new View();
        return $view->render($name, $data);
    }

    // 视图前缀
    protected function viewPrefix()
    {
        return \mix\base\Route::camelToSnake(str_replace([\Mix::app()->controllerNamespace . '\\', '\\', 'Controller'], ['', '.', ''], get_class($this)));
    }

}
