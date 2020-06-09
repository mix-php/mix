<?php

namespace Mix\Micro\Route;

use Mix\Http\Server\ServerHandlerInterface;

/**
 * Interface RouterInterface
 * @package Mix\Micro\Route
 */
interface RouterInterface extends ServerHandlerInterface
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
    public function services();

}
