<?php

namespace Mix\Redis;

/**
 * Trait ScanTrait
 * @package Mix\Redis
 */
trait ScanTrait
{

    /**
     * 遍历key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return array|bool
     */
    public function scan(&$iterator, $pattern = '', $count = 0)
    {
        // $iterator 必须要加 &
        return $this->__call(__FUNCTION__, [&$iterator, $pattern, $count]);
    }

    /**
     * 遍历set key
     * @param $key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return array|bool
     */
    public function sScan($key, &$iterator, $pattern = '', $count = 0)
    {
        // $iterator 必须要加 &
        return $this->__call(__FUNCTION__, [$key, &$iterator, $pattern, $count]);
    }

    /**
     * 遍历zset key
     * @param $key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return array|bool
     */
    public function zScan($key, &$iterator, $pattern = '', $count = 0)
    {
        // $iterator 必须要加 &
        return $this->__call(__FUNCTION__, [$key, &$iterator, $pattern, $count]);
    }

    /**
     * 遍历hash key
     * @param $key
     * @param $iterator
     * @param string $pattern
     * @param int $count
     * @return array
     */
    public function hScan($key, &$iterator, $pattern = '', $count = 0)
    {
        // $iterator 必须要加 &
        return $this->__call(__FUNCTION__, [$key, &$iterator, $pattern, $count]);
    }

}
