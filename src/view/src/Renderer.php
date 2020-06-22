<?php

namespace Mix\View;

use Mix\View\Exception\ViewException;

/**
 * Class Renderer
 * @package Mix\View
 * @author liu,jian <coder.keda@gmail.com>
 */
class Renderer
{

    /**
     * @var string
     */
    protected $__dir__;

    /**
     * @var string
     */
    public $title;

    /**
     * Renderer constructor.
     * @param string $dir
     */
    public function __construct(string $dir)
    {
        $this->__dir__ = $dir;
    }

    /**
     * 渲染视图
     * @param $__template__
     * @param $__data__
     * @return string
     */
    public function render($__template__, $__data__)
    {
        // 兼容老版本
        if (func_num_args() == 3) {
            list($__viewdir__, $__template__, $__data__) = func_get_args();
        }

        // 传入变量
        extract($__data__);
        // 生成视图
        $__filepath__ = $this->__dir__ . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $__template__) . '.php';
        if (!is_file($__filepath__)) {
            throw new ViewException("View file does not exist: {$__filepath__}");
        }
        ob_start();
        include $__filepath__;
        return ob_get_clean();
    }

}
