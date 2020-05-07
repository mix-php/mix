<?php

namespace Mix\Http\Server\Helper;

/**
 * Class ServerHelper
 * @package Mix\Http\Server\Helper
 */
class ServerHelper
{

    /**
     * 获取当前时间, 单位: 秒, 粒度: 微秒
     * @return float
     */
    public static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}
