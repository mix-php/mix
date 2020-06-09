<?php

namespace Mix\Bean;

use Mix\Bean\Exception\NotFoundException;

/**
 * Class BeanFactory
 * @package Mix\Bean
 * @author liu,jian <coder.keda@gmail.com>
 */
Trait BeanFactoryTrait
{

    /**
     * Bean配置
     * @var array
     */
    public $config = [];

    /**
     * Bean数组
     * @var BeanDefinition[]
     */
    protected $definitions = [];

    /**
     * 单例池
     * @var &object[]
     */
    protected $objects = [];

    /**
     * 解析
     */
    public function parse()
    {
        $definitions = [];
        foreach ($this->config as $item) {
            $definition         = new BeanDefinition($item);
            $name               = $definition->getName();
            $definitions[$name] = $definition;
        }
        $this->definitions = $definitions;
    }

    /**
     * 装载
     * 实例化所有单例 (Scope == SINGLETON)
     * 提前装载能暴露出有异常的代码，便于发现问题
     * @deprecated 移除
     */
    public function load()
    {
        foreach ($this->definitions as $definition) {
            if ($definition->getScope() == BeanDefinition::SINGLETON) {
                $this->get($definition->getName());
            }
        }
    }

    /**
     * 获取BeanDefinition
     * @param $beanName
     * @return BeanDefinition
     */
    public function getBeanDefinition(string $beanName): BeanDefinition
    {
        if (!isset($this->definitions[$beanName])) {
            throw new NotFoundException("Bean definition '{$beanName}' not found");
        }
        return $this->definitions[$beanName];
    }

    /**
     * 获取Bean
     * @param string $beanName
     * @param array $config
     * @return object
     */
    public function getBean(string $beanName, array $config = [])
    {
        $definition              = $this->getBeanDefinition($beanName);
        $definition->beanFactory = $this;
        // singleton
        if ($definition->getScope() == BeanDefinition::SINGLETON) {
            if (isset($this->objects[$beanName])) {
                return $this->objects[$beanName];
            }
            $object                   = $this->newInstance($definition, $config);
            $definition->object       = &$object;
            $this->objects[$beanName] = &$object;
            return $object;
        }
        // prototype
        return $this->newInstance($definition, $config);
    }

    /**
     * 创建实例
     * @param $config
     * @return object
     */
    protected function newInstance(BeanDefinition $definition, array $config)
    {
        // 配置分类
        $coverConstructorArgs = [];
        $coverProperties      = [];
        foreach ($config as $key => $value) {
            if (is_numeric($key)) {
                if (is_null($value)) {
                    continue;
                }
                $coverConstructorArgs[$key] = $value;
            } else {
                $coverProperties[$key] = $value;
            }
        }
        // 创建实例
        $class           = $definition->getClass();
        $properties      = $definition->getProperties();
        $constructorArgs = $definition->getConstructorArgs();
        $initMethod      = $definition->getInitMethod();
        $object          = null;
        if ($constructorArgs) {
            $constructorArgs = $coverConstructorArgs + $constructorArgs;
            // 支持构造参数中的数组参数中的ref的依赖引用
            foreach ($constructorArgs as $key => $arg) {
                if (!(is_array($arg) && isset($arg['ref']))) {
                    continue;
                }
                $constructorArgs[$key] = BeanInjector::build($this, $arg);
            }
            $object = new $class(...$constructorArgs);
        }
        if ($properties) {
            $properties = $coverProperties + $properties;
            $properties = BeanInjector::build($this, $properties);
            $object or $object = new $class();
            BeanInjector::inject($object, $properties);
        }
        if (!$object) {
            $object = new $class();
        }
        $initMethod and call_user_func([$object, $initMethod]);
        return $object;
    }

}
