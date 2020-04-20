<?php

namespace Mix\Micro\Route;

/**
 * Class Router
 * @package Mix\Micro\Route
 */
class Router extends \Mix\Route\Router
{

    /**
     * 获取 url 规则映射的全部 service 名称
     *
     * Url                  Service
     * /                    index
     * /foo                 foo
     * /foo/bar             foo
     * /foo/bar/baz         foo
     * /foo/bar/baz/cat     foo.bar
     * /v1/foo/bar          v1.foo
     * /v1/foo/bar/baz      v1.foo
     * /v1/foo/bar/baz/cat  v1.foo.bar
     *
     * @return string[]
     */
    public function services()
    {
        $services = [];
        foreach ($this->materials as $material) {
            $regular = $material[0];
            $slice   = explode(' ', $regular);
            $path    = substr($slice[1], 0, -3);
            $slice   = array_filter(explode('\/', strtolower($path)));
            $version = '';
            if (isset($slice[1]) && stripos($slice[1], 'v') === 0) {
                $version = array_shift($slice) . '.';
            }
            switch (count($slice)) {
                case 0:
                    $name = 'index';
                    break;
                case 1:
                case 2:
                case 3:
                    $name = array_shift($slice);
                    break;
                default:
                    array_pop($slice);
                    array_pop($slice);
                    $name = implode('.', $slice);
            }
            $services[] = $version . $name;
        }
        return $services;
    }

}
