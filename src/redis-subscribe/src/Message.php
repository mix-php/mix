<?php

namespace Mix\Redis\Subscribe;

/**
 * Class Message
 * @package Mix\Redis\Subscribe
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