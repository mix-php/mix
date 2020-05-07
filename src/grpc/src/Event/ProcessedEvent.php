<?php

namespace Mix\Grpc\Event;

use Google\Protobuf\Internal\Message;

/**
 * Class ProcessedEvent
 * @package Mix\Grpc\Event
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
     * @var Message|null
     */
    public $request;

    /**
     * 响应
     * @var Message|null
     */
    public $response;

    /**
     * 服务
     * @var string
     */
    public $service;

    /**
     * 方法
     * @var string Foo.Bar
     */
    public $method;

    /**
     * 执行异常信息
     * @var string|null
     */
    public $error;

}
