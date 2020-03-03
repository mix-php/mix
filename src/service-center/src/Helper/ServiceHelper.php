<?php

namespace Mix\ServiceCenter\Helper;

/**
 * Class ServiceHelper
 * @package Mix\ServiceCenter\Helper
 */
class ServiceHelper
{

    /**
     * Create uuid
     * @return string
     */
    public static function uuid()
    {
        return (new UuidFactory())->uuid1(crc32(static::ip()))->toString();
    }

    /**
     * Get local IP
     * @return string
     */
    public static function localIP()
    {
        return current(swoole_get_local_ip());
    }

}
