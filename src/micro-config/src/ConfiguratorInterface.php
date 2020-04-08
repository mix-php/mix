<?php

namespace Mix\Micro\Config;

/**
 * Interface ConfiguratorInterface
 * @package Mix\Micro\Config
 */
interface ConfiguratorInterface
{

    /**
     * Put
     * @param array $kvs
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function put(array $kvs);

    /**
     * Pull config
     * @return string[]
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function pull();

    /**
     * 监听配置变化
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function listen();

    /**
     * Close
     */
    public function close();

}
