<?php

namespace Mix\Bean;

use Mix\Bean\Exception\InjectException;

/**
 * Class BeanInjector
 * @package Mix\Bean
 * @author liu,jian <coder.keda@gmail.com>
 */
class BeanInjector
{

    /**
     * 构建
     * @param BeanFactoryInterface $beanFactory
     * @param array $config
     * @return array|object
     */
    public static function build(BeanFactoryInterface $beanFactory, array $config)
    {
        foreach ($config as $key => $value) {
            // 子类处理
            if (is_array($value)) {
                if (array_values($value) === $value) {
                    // 非关联数组
                    foreach ($value as $subNumberKey => $subValue) {
                        if (!(is_array($subValue) && isset($subValue['ref']))) {
                            continue;
                        }
                        $config[$key][$subNumberKey] = static::build($beanFactory, $subValue);
                    }
                } else {
                    // 引用依赖
                    if (isset($value['ref'])) {
                        $config[$key] = static::build($beanFactory, $value);
                    }
                }
            } elseif ($key === 'ref') {
                // 引用依赖实例化
                return $beanFactory->getBean($config['ref']);
            }
        }
        return $config;
    }

    /**
     * 注入属性
     * @param $object
     * @param array $properties
     * @return mixed
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public static function inject($object, array $properties)
    {
        // 解决 BeanInjector 使用了 PhpDocReader UseStatementParser::getFileContent 获取注释类型时使用了 file 操作，会导致 SWOOLE_HOOK_FILE 切换协程，使单例失效
        $canRuntime = (extension_loaded('swoole') and class_exists('Swoole\Runtime'));
        $flags      = 1879048191; // 1879048191 = SWOOLE_HOOK_ALL
        if (method_exists('Swoole\Runtime', 'getHookFlags')) {
            $flags = \Swoole\Runtime::getHookFlags();
        }
        $canRuntime and \Swoole\Runtime::enableCoroutine($flags == 0 ? $flags : $flags ^ 256); // 256 = SWOOLE_HOOK_FILE

        foreach ($properties as $name => $value) {
            // 注释类型检测
            $class      = get_class($object);
            $reflection = new \ReflectionClass($class);
            if (!$reflection->hasProperty($name)) {
                throw new InjectException(sprintf('Undefined property: %s::$%s', $class, $name));
            }
            $property      = $reflection->getProperty($name);
            $reader        = new PhpDocReader();
            $propertyClass = $reader->getPropertyClass($property);
            if (!$propertyClass) {
                // 导入
                $object->$name = $value;
                continue;
            }
            $start = strpos($propertyClass, '[');
            if ($start !== false) {
                // 当前的doc标注里面这是一个数组，去掉数组的尾巴
                $propertyClass = substr($propertyClass, 0, $start);
                // 这时候当前的$value已经是个被依赖注入自动维护的实例数组了 不需要特殊处理
                $values = $value;
            } else {
                // 不是数组，弄成临时数组 方便下面遍历检查
                $values = [$value];
            }
            foreach ($values as $val) {
                if (isset($val) && !($val instanceof $propertyClass)) {
                    throw new InjectException("The type of the imported property does not match, class: {$class}, property: {$name}, @var: {$propertyClass}");
                }
            }
            // 导入
            $object->$name = $value;
        }

        $canRuntime and \Swoole\Runtime::enableCoroutine($flags);

        return $object;
    }

}
