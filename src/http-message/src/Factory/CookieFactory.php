<?php

namespace Mix\Http\Message\Factory;

use Mix\Http\Message\Cookie;

/**
 * Class CookieFactory
 * @package Mix\Http\Message\Factory
 * @author liu,jian <coder.keda@gmail.com>
 */
class CookieFactory
{

    /**
     * Create cookie
     * @param string $method
     * @param $uri
     * @return Cookie
     */
    public function createCookie(string $name, string $value = '', int $expire = 0): Cookie
    {
        return new Cookie($name, $value, $expire);
    }

}
