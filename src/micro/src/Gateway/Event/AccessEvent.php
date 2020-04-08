<?php

namespace Mix\Micro\Gateway\Event;

use Mix\Micro\Register\ServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AccessEvent
 * @package Mix\Micro\Gateway\Event
 */
class AccessEvent
{

    /**
     * 执行时间 (ms)
     * @var float
     */
    public $time;

    /**
     * 响应的状态码
     * @var int
     */
    public $status;

    /**
     * 请求对象
     * @var ServerRequestInterface
     */
    public $request;

    /**
     * 响应对象
     * @var ResponseInterface
     */
    public $response;

    /**
     * 服务对象
     * @var ServiceInterface
     */
    public $service;

    /**
     * 执行异常信息
     * @var string
     */
    public $error;

}
