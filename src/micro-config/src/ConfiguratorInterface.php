<?php

namespace Mix\Micro\Config;

/**
 * Interface ConfiguratorInterface
 * @package Mix\Micro\Config
 */
interface ConfiguratorInterface
{

    /**
     * Listen
     * @param EventDispatcherInterface $dispatcher
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function listen(EventDispatcherInterface $dispatcher);

    /**
     * Sync to config-server
     * 可在 git webhook 中调用某个接口来触发该方法
     * @param string $path 目录或者文件路径
     * @param string $prefix
     */
    public function sync(string $path);

    /**
     * All
     * @return string[]
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function all();

    /**
     * Close
     */
    public function close();

}
