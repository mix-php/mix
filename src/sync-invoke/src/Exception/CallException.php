<?php

namespace Mix\SyncInvoke\Exception;

/**
 * Class CallException
 * @package Mix\SyncInvoke\Exception
 */
class CallException
{

    /**
     * @var string
     */
    public $message;

    /**
     * @var int
     */
    public $code;
    
    /**
     * CallException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message, int $code)
    {
        $this->message = $message;
        $this->code    = $code;
    }

}
