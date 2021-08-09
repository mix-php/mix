<?php

namespace Mix\Redis\Subscriber;

/**
 * Class Message
 * @package Mix\Redis\Subscriber
 */
class Message
{

    /**
     * @var string
     */
    public $channel;

    /**
     * @var string
     */
    public $payload;

}