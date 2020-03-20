<?php

namespace Mix\Bean;

/**
 * Interface BeanFactoryInterface
 * @package Mix\Bean
 * @author liu,jian <coder.keda@gmail.com>
 */
interface BeanFactoryInterface
{

    /**
     * 获取BeanDefinition
     * @param $beanName
     * @return BeanDefinition
     */
    public function getBeanDefinition(string $beanName): BeanDefinition;

    /**
     * 获取Bean
     * @param string $beanName
     * @param array $config
     * @return object
     */
    public function getBean(string $beanName, array $config = []);

}
