<?php

/**
 * 异常基类
 * @author 刘健 <code.liu@qq.com>
 */

namespace mix\exception;

class BaseException extends \RuntimeException
{

    // HTTP状态码
    public $statusCode = 500;

    // 构造
    public function __construct($message = '', $statusCode = 0)
    {
        empty($message) or $this->message = $message;
        empty($statusCode) or $this->statusCode = $statusCode;
        // 父类构造
        parent::__construct();
    }

}
