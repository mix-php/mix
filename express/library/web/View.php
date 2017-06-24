<?php

/**
 * View类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\web;

class View
{

    // 模板变量
    private $data = [];

    // 模板地址
    private $template;

    public function __construct($template = null, $data = [])
    {
        $this->template = $template;
        $this->data     = $data;
    }

    // 创建实例
    public static function create($template = null, $data = [])
    {
        return new self($template, $data);
    }

    // 设置模板地址
    public function fetch($template)
    {
        $this->template = $template;
        return $this;
    }

    // 变量赋值
    public function assign($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    // 输出
    public function output()
    {
        echo self::import($this->template, $this->data);
        exit;
    }

    // 导入视图文件
    protected static function import($__template__, $__data__)
    {
        // 传入变量
        foreach ($__data__ as $__key__ => $__value__) {
            $$__key__ = $__value__;
        }
        // 生成视图
        $__filepath__ = APP_PATH . str_replace('.', DS, $__template__) . '.php';
        if (!is_file($__filepath__)) {
            throw new \sys\exception\ViewException('视图文件不存在', $__template__);
        }
        include $__filepath__;
        return ob_get_clean();
    }

    // 判断视图是否存在
    public static function has($template)
    {
        $filePath = APP_PATH . str_replace('.', DS, $template) . '.php';
        return is_file($filePath);
    }

}
