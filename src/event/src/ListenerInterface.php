<?php declare(strict_types=1);

namespace Mix\Event;

/**
 * Interface ListenerInterface
 * @package Mix\Event
 */
interface ListenerInterface
{

    /**
     * 监听的事件
     * @return array
     */
    public function events(): array;

    /**
     * 处理事件
     * @param object $event
     */
    public function process(object $event): void;

}
