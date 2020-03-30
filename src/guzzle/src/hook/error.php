<?php

namespace GuzzleHttp\Handler {

    /**
     * 重写写系统方法，使其失效
     * @param callable $call
     */
    function set_error_handler(callable $call)
    {
    }

    /**
     * 重写系统方法，使其失效
     */
    function restore_error_handler()
    {
    }

}

namespace GuzzleHttp\Psr7 {

    /**
     * 重写写系统方法，使其失效
     * @param callable $call
     */
    function set_error_handler(callable $call)
    {
    }

    /**
     * 重写系统方法，使其失效
     */
    function restore_error_handler()
    {
    }

}
