<?php

/**
 * SysView类
 * @author 刘健 <code.liu@qq.com>
 */

namespace sys\web;

class Error
{

    // 模板变量
    private $data = [];

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    // 创建实例
    public static function create($data = [])
    {
        return new self($data);
    }

    // 输出
    public function output()
    {
        echo self::import($this->data);
        exit;
    }

    // 导入模板文件
    protected static function import($data)
    {
        $template = 'exception';
        // 传入变量
        foreach ($data as $key => $value) {
            $$key = $value;
        }
        // 生成视图
        $filePath = TPL_PATH . str_replace('.', DS, $template) . '.php';
        if (!is_file($filePath)) {
            throw new \sys\exception\TemplateException('模板文件不存在', $template);
        }
        include $filePath;
        return ob_get_clean();
    }

}
