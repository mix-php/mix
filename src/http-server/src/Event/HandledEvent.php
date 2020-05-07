<?php

namespace Mix\Http\Server\Event;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Class HandledEvent
 * @package Mix\Http\Server\Event
 */
class HandledEvent
{

    /**
     * 执行时间 (ms)
     * @var float
     */
    public $time;

    /**
     * 请求
     * @var ServerRequest
     */
    public $request;

    /**
     * 响应
     * @var Response
     */
    public $response;

    /**
     * 执行异常信息
     * @var string|null
     */
    public $error;

}
