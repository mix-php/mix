<?php

namespace Mix\Micro\Gateway\Helper;

use Mix\Http\Message\Cookie\Cookie;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class ProxyHelper
 * @package Mix\Micro\Gateway\Helper
 */
class ProxyHelper
{

    /**
     * Get request uri
     * @param UriInterface $uri
     * @return string
     */
    public static function getRequestUri(UriInterface $uri)
    {
        $path     = $uri->getPath();
        $query    = $uri->getQuery();
        $query    = $query ? "?{$query}" : '';
        $fragment = $uri->getFragment();
        $fragment = $fragment ? "#{$fragment}" : '';
        $full     = $path . $query . $fragment;
        return $full;
    }

    /**
     * 判断是否为 websocket
     * @return bool
     */
    public static function isWebSocket(ServerRequestInterface $request)
    {
        if ($request->getHeaderLine('connection') !== 'Upgrade' || $request->getHeaderLine('upgrade') !== 'websocket') {
            return false;
        }
        return true;
    }

    /**
     * Parse cookie
     * @param string $header
     * @return Cookie
     */
    public static function parseCookie(string $header)
    {
        $name     = '';
        $value    = '';
        $expire   = 0;
        $path     = '/';
        $domain   = '';
        $secure   = false;
        $httpOnly = false;
        foreach (explode('; ', $header) as $k => $v) {
            if ($k == 0) {
                list($name, $value) = explode('=', $v);
            }
            if (strpos($v, 'path=') === 0) {
                list(, $path) = explode('=', $v);
            }
            if (strpos($v, 'expires=') === 0) {
                list(, $gmt) = explode('=', $v);
                $expire = strtotime($gmt);
            }
            if (strpos($v, 'domain=') === 0) {
                list(, $domain) = explode('=', $v);
            }
            if ($v == 'secure') {
                $secure = true;
            }
            if ($v == 'httponly') {
                $httpOnly = true;
            }
        }
        return new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

}
