<?php

namespace Mix\Bean;

use Mix\Bean\Exception\ScopeException;

/**
 * Class BeanDefinition
 * @package Mix\Bean
 * @author liu,jian <coder.keda@gmail.com>
 */
class BeanDefinition
{

    /**
     * Scope
     */
    const PROTOTYPE = 'prototype';
    const SINGLETON = 'singleton';

    /**
     * @var BeanFactoryInterface
     */
    public $beanFactory;

    /**
     * @var array
     */
    public $config;

    /**
     * BeanDefinition constructor.
     * @param BeanFactoryInterface $beanFactory
     * @param array $config
     */
    public function __construct(BeanFactoryInterface $beanFactory, array $config)
    {
        // 导入属性
        $this->beanFactory = $beanFactory;
        $this->config      = $config;
    }

    /**
     * 初始化后执行指定方法
     * @return string
     */
    public function getInitMethod(): string
    {
        $config = $this->config;
        if (isset($config['initMethod'])) {
            return $config['initMethod'];
        }
        return '';
    }

    /**
     * 获取名称
     * @return string
     */
    public function getName(): string
    {
        $config = $this->config;
        if (isset($config['name'])) {
            return $config['name'];
        }
        return $config['class'];
    }

    /**
     * 获取类名
     * @return string
     */
    public function getClass(): string
    {
        $config = $this->config;
        $class  = $config['class'];
        return $class;
    }

    /**
     * 获取作用域
     * @return string
     */
    public function getScope(): string
    {
        $config = $this->config;
        if (isset($config['scope'])) {
            $scope = $config['scope'];
            if (!in_array($scope, [static::PROTOTYPE, static::SINGLETON])) {
                throw new ScopeException('Scope can only be [' . static::PROTOTYPE . ', ' . static::SINGLETON . ']');
            }
            return $scope;
        }
        return static::PROTOTYPE;
    }

    /**
     * 获取属性
     * @return array
     */
    public function getProperties(): array
    {
        $config     = $this->config;
        $properties = $config['properties'] ?? [];
        return $properties;
    }

    /**
     * 获取构造参数
     * @return array
     */
    public function getConstructorArgs(): array
    {
        $config = $this->config;
        $args   = $config['constructorArgs'] ?? [];
        return $args;
    }

    /**
     * 创建实例
     * @param $config
     * @return object
     */
    public function newInstance(array $config)
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
        $class           = $this->getClass();
        $properties      = $this->getProperties();
        $constructorArgs = $this->getConstructorArgs();
        $initMethod      = $this->getInitMethod();
        $object          = null;
        if ($constructorArgs) {
            $constructorArgs = $coverConstructorArgs + $constructorArgs;
            // 支持构造参数中的数组参数中的ref的依赖引用
            foreach ($constructorArgs as $key => $arg) {
                if (is_scalar($arg)) {
                    continue;
                }
                $constructorArgs[$key] = BeanInjector::build($this->beanFactory, $arg);
            }
            $object = new $class(...$constructorArgs);
        }
        if ($properties) {
            $properties = $coverProperties + $properties;
            $properties = BeanInjector::build($this->beanFactory, $properties);
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
