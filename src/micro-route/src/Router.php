<?php

namespace Mix\Micro\Route;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Class Router
 * @package Mix\Micro\Route
 * @deprecated 废弃，请使用 FastRoute 替代
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
     * @return string[][] [name => [pattern,...]]
     */
    public function services()
    {
        $services = [];
        foreach ($this->materials as $material) {
            list($regular, , , $pattern) = $material;
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
            $services[$version . $name][] = $pattern;
        }
        return $services;
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param Response $response
     * @throws \Throwable
     */
    public function handleHTTP(ServerRequest $request, Response $response)
    {
        // 支持 micro web 的代理
        // micro web 代理无法将 /foo/ 后面的杠传递过来
        $basePath   = $request->getHeaderLine('x-micro-web-base-path');
        $isMicroWeb = $basePath ? true : false;
        if ($isMicroWeb) {
            $uri = $request->getUri();
            $uri->withPath(sprintf('%s%s', $basePath, $uri->getPath() == '/' ? '' : $uri->getPath()));
            $serverParams                = $request->getServerParams();
            $serverParams['request_uri'] = sprintf('%s%s', $basePath, $serverParams['request_uri'] == '/' ? '' : $serverParams['request_uri']);
            $serverParams['path_info']   = sprintf('%s%s', $basePath, $serverParams['path_info'] == '/' ? '' : $serverParams['path_info']);
            $request->withServerParams($serverParams);
        }

        return parent::handleHTTP($request, $response);
    }

}
