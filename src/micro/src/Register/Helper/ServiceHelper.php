<?php

namespace Mix\Micro\Register\Helper;

use Ramsey\Uuid\UuidFactory;

/**
 * Class ServiceHelper
 * @package Mix\Micro\Register\Helper
 */
class ServiceHelper
{

    /**
     * Create uuid
     * @return string
     * @throws \Exception
     */
    public static function uuid()
    {
        return (new UuidFactory())->uuid1(crc32(static::localIP()))->toString();
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
