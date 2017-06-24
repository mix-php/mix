<?php

/**
 * TemplateException类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\exception;

class TemplateException extends NotFoundException
{

    public function __construct($message, $location)
    {
        parent::__construct($message, $location);
    }

}
