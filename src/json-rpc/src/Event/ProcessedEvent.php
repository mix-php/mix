<?php

namespace Mix\JsonRpc\Event;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Class ProcessedEvent
 * @package Mix\JsonRpc\Event
 */
class ProcessedEvent
{

    /**
     * 执行时间 (ms)
     * @var float
     */
    public $time;

    /**
     * 请求
     * @var Request
     */
    public $request;

    /**
     * 响应
     * @var Response
     */
    public $response;

    /**
     * 服务
     * @var string
     */
    public $service;

    /**
     * 执行异常信息
     * @var string
     */
    public $error;

}
