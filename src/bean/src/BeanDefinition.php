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
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $initMethod;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $constructorArgs = [];

    /**
     * @var BeanFactoryInterface
     */
    public $beanFactory;

    /**
     * @var &object
     */
    public $object;

    /**
     * BeanDefinition constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * 获取名称
     * @return string
     */
    public function getName(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }
        return $this->class;
    }

    /**
     * 初始化后执行指定方法
     * @return string
     */
    public function getInitMethod(): string
    {
        if (isset($this->initMethod)) {
            return $this->initMethod;
        }
        return '';
    }

    /**
     * 获取类名
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * 获取作用域
     * @return string
     */
    public function getScope(): string
    {
        if (isset($this->scope)) {
            $scope = $this->scope;
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
        return $this->properties ?? [];
    }

    /**
     * 设置全部属性
     * @param array $properties
     */
    public function withProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * 设置单个属性
     * @param string $key
     * @param $value
     */
    public function withPropertie(string $key, $value)
    {
        $this->properties[$key] = $value;
    }

    /**
     * 获取构造参数
     * @return array
     */
    public function getConstructorArgs(): array
    {
        return $this->constructorArgs ?? [];
    }

    /**
     * 设置构造参数
     * @param array $args
     */
    public function withConstructorArgs(array $args)
    {
        $this->constructorArgs = $args;
    }

    /**
     * 加载或刷新
     * 只作用于单例，如果单例已经存在将被销毁再重新加载
     */
    public function refresh()
    {
        if (!isset($this->object) || !isset($this->beanFactory)) {
            return;
        }
        $this->object = null;
        $this->beanFactory->getBean($this->getName());
    }

}
