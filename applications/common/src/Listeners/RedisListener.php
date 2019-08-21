<?php

namespace Common\Listeners;

use Mix\Event\ListenerInterface;
use Mix\Redis\Event\ExecuteEvent;

/**
 * Class RedisListener
 * @package Common\Listeners
 * @author liu,jian <coder.keda@gmail.com>
 */
class RedisListener implements ListenerInterface
{

    /**
     * 监听的事件
     * @return array
     */
    public function events(): array
    {
        // TODO: Implement events() method.
        return [
            ExecuteEvent::class,
        ];
    }

    /**
     * 处理事件
     * @param object $event
     * @return mixed|void
     */
    public function process(object $event)
    {
        // TODO: Implement process() method.
    }

}
