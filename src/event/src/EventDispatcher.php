<?php declare(strict_types=1);

namespace Mix\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Class EventDispatcher
 * @package Mix\Event
 * @author liu,jian <coder.keda@gmail.com>
 */
class EventDispatcher implements EventDispatcherInterface
{

    /**
     * @var ListenerProviderInterface
     */
    public $listenerProvider;

    /**
     * EventDispatcher constructor.
     * @param string ...$listeners
     */
    public function __construct(string ...$listeners)
    {
        $objects = [];
        foreach ($listeners as $listener) {
            $objects[] = new $listener;
        }
        $this->listenerProvider = new ListenerProvider(...$objects);
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event
     *   The object to process.
     *
     * @return object
     *   The Event that was passed, now modified by listeners.
     */
    public function dispatch(object $event)
    {
        foreach ($this->listenerProvider->getListenersForEvent($event) as $callback) {
            call_user_func($callback, $event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }

}
