<?php

namespace Mix\Console\Exception;

/**
 * Class ErrorException
 * @package Mix\Exception
 * @author liu,jian <coder.keda@gmail.com>
 */
class ErrorException extends \RuntimeException
{

    // 构造
    public function __construct($type, $message, $file, $line)
    {
        $this->code = $type;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
        // 父类构造
        parent::__construct();
    }

}
