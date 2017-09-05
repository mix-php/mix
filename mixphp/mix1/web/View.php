<?php

/**
 * View类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\web;

class View
{

    // 标题
    public $title;

    // 导入视图文件
    public function import($__template__, $__data__)
    {
        // 传入变量
        extract($__data__);
        // 生成视图
        $__filepath__ = \Mix::$app->getViewPath() . str_replace('.', DS, $__template__) . '.php';
        if (!is_file($__filepath__)) {
            throw new \mix\exception\ViewException("视图文件不存在：{$__filepath__}");
        }
        ob_start();
        include $__filepath__;
        return ob_get_clean();
    }

}
