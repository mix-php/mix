<?php

/**
 * ErrorException类
 * @author 刘健 <code.liu@qq.com>
 */

namespace express\exception;

class ErrorException extends \RuntimeException
{

    public function __construct($type, $message, $file, $line)
    {
        $this->code = $type;
        $this->message  = $message;
        $this->file = $file;
        $this->line = $line;
    }

}
