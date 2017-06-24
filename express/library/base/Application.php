<?php

/**
 * App类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\base;

class Application
{

    // 应用根路径
    public $basePath;
    // 注册树配置
    public $register;

    /**
     * 构造
     * @param array $config
     */
    public function __construct($config)
    {
        // 添加属性
        $this->basePath = $config['basePath'];
        $this->register = $config['register'];
        // 快捷引用
        \Express::$app = $this;
    }

    /**
     * 注册树实现
     * @param  string $name
     */
    public function __get($name)
    {
        if (!isset($this->$name)) {
            // 实例化
            $list        = $this->register[$name];
            $class       = $list['class'];
            $this->$name = new $class();
            // 属性导入
            foreach ($list as $key => $value) {
                if ($key == 'class') {
                    continue;
                }
                $this->$name->$key = $value;
            }
            // 执行初始化
            method_exists($this->$name, 'init') and $this->$name->init();
        }
        return $this->$name;
    }

    public function run()
    {
        $pathinfo = empty($_SERVER['PATH_INFO']) ? '' : substr($_SERVER['PATH_INFO'], 1);
        $response = \Express::$app->route->runAction($pathinfo, ['get' => $_GET, 'post' => $_POST]);
        print_r($response);
    }

    public function runAction($action, $requestParams = [])
    {
        return \Express::$app->route->runAction($action, $requestParams);
    }

    /**
     * 获取配置路径
     * @return string
     */
    public function getConfigPath()
    {
        return $this->basePath . 'config' . DS;
    }

    /**
     * 获取运行路径
     * @return string
     */
    public function getRuntimePath()
    {
        return $this->basePath . 'runtime' . DS;
    }

    /**
     * 获取视图路径
     * @return string
     */
    public function getViewPath()
    {
        return $this->basePath . 'view' . DS;
    }

}
