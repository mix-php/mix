<?php

namespace mix\exception;

/**
 * NotFoundException类
 * @author 刘健 <coder.liu@qq.com>
 */
class NotFoundException extends HttpException
{

    // HTTP状态码
    protected $_statusCode = 404;

}
