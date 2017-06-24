<?php

/**
 * NotFoundException类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\exception;

class NotFoundException extends \RuntimeException
{

    protected $location;

    public function __construct($message, $location)
    {
        $this->message  = $message;
        $this->location = $location;
    }

    // 获取位置
    public function getLocation()
    {
        return $this->location;
    }

}
