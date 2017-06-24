<?php

/**
 * HttpException类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\exception;

class HttpException extends \RuntimeException
{

	protected $statusCode;

    public function __construct($statusCode, $message)
    {
    	$this->statusCode  = $statusCode;
    	$this->message  = $message;
    }

    // 获取状态码
    public function getStatusCode()
    {
        return $this->statusCode;
    }

}
