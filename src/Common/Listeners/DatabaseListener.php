<?php

namespace App\Common\Listeners;

use Mix\Database\Event\ExecuteEvent;
use Mix\Event\ListenerInterface;

/**
 * Class DatabaseListener
 * @package App\Common\Listeners
 * @author liu,jian <coder.keda@gmail.com>
 */
class DatabaseListener implements ListenerInterface
{

    /**
     * 监听的事件
     * @return array
     */
    public function events(): array
    {
        // TODO: Implement events() method.
        // 要监听的事件数组，可监听多个事件
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
        // 事件触发后，会执行该方法
    }

}
