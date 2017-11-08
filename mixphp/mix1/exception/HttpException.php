<?php

namespace mix\exception;

/**
 * HttpException类
 * @author 刘健 <coder.liu@qq.com>
 */
class HttpException extends \RuntimeException
{

    // HTTP状态码
    protected $_statusCode;

    // 获取状态码
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

}
