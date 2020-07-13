<?php declare(strict_types=1);

namespace Mix\Event;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Class ListenerProvider
 * @package Mix\Event
 * @author liu,jian <coder.keda@gmail.com>
 */
class ListenerProvider implements ListenerProviderInterface
{

    /**
     * @var [][]ListenerInterface
     */
    protected $eventListeners = [];

    /**
     * EventDispatcher constructor.
     * @param ListenerInterface ...$listeners
     */
    public function __construct(ListenerInterface ...$listeners)
    {
        $eventListeners = [];
        foreach ($listeners as $listener) {
            foreach ($listener->events() as $event) {
                $eventListeners[$event][] = $listener;
            }
        }
        $this->eventListeners = $eventListeners;
    }

    /**
     * @param object $event
     *   An event for which to return the relevant listeners.
     * @return iterable[callable]
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(object $event): iterable
    {
        $class     = get_class($event);
        $listeners = $this->eventListeners[$class] ?? [];
        $iterable  = [];
        foreach ($listeners as $listener) {
            $iterable[] = [$listener, 'process'];
        }
        return $iterable;
    }

}
