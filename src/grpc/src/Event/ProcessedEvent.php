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
     * @var Message
     */
    public $request;

    /**
     * 响应
     * @var Message
     */
    public $response;

    /**
     * 端点
     * @var array ['service'=>'','method'=>'']
     */
    public $endpoint;

    /**
     * 执行异常信息
     * @var string
     */
    public $error;

}
