<?php

namespace mix\base;

/**
 * 配置组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Config extends Component
{

    // 配置文件参数
    public $params = [];

    // 自动加载列表
    public $autoload = [];

    // 初始化
    public function onInitialize()
    {
        parent::onInitialize();
        // 自动加载
        foreach ($this->autoload as $file) {
            $this->load($file);
        }
    }

    // 导入配置文件
    protected static function import($file)
    {
        $filePath = \Mix::app()->getConfigPath() . $file . '.php';
        if (!is_file($filePath)) {
            throw new \mix\exception\ConfigException("配置文件不存在：{$file}.php");
        }
        $params = include $filePath;
        return is_array($params) ? $params : [];
    }

    // 加载配置
    public function load($file)
    {
        if (!isset($this->params[$file])) {
            $this->params[$file] = self::import($file);
        }
    }

    // 读取配置
    public function get($name = '')
    {
        // 全部配置
        if ($name == '') {
            return $this->params;
        }
        $array = explode('.', $name);
        $level = count($array);
        // 一级配置
        if ($level == 1) {
            list($oneLevel) = $array;
            if (isset($this->params[$oneLevel])) {
                return $this->params[$oneLevel];
            }
        }
        // 二级配置
        if ($level == 2) {
            list($oneLevel, $secondLevel) = $array;
            if (isset($this->params[$oneLevel][$secondLevel])) {
                return $this->params[$oneLevel][$secondLevel];
            }
        }
        // 三级配置
        if ($level == 3) {
            list($oneLevel, $secondLevel, $threeLevel) = $array;
            if (isset($this->params[$oneLevel][$secondLevel][$threeLevel])) {
                return $this->params[$oneLevel][$secondLevel][$threeLevel];
            }
        }
        throw new \mix\exception\ConfigException("配置项不存在：{$name}");
    }

    // 判断配置是否存在
    public function has($name = '')
    {
        $array = explode('.', $name);
        $level = count($array);
        // 一级配置
        if ($level == 1) {
            list($oneLevel) = $array;
            return isset($this->params[$oneLevel]) ? true : false;
        }
        // 二级配置
        if ($level == 2) {
            list($oneLevel, $secondLevel) = $array;
            return isset($this->params[$oneLevel][$secondLevel]) ? true : false;
        }
        // 三级配置
        if ($level == 3) {
            list($oneLevel, $secondLevel, $threeLevel) = $array;
            return isset($this->params[$oneLevel][$secondLevel][$threeLevel]) ? true : false;
        }
        return false;
    }

}
