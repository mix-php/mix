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
     * @var []ListenerInterface
     */
    protected $listeners = [];

    /**
     * EventDispatcher constructor.
     * @param ListenerInterface ...$listeners
     */
    public function __construct(ListenerInterface ...$listeners)
    {
        $this->listeners = $listeners;
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
        $class    = get_class($event);
        $iterable = [];
        foreach ($this->listeners as $listener) {
            if (in_array($class, $listener->events())) {
                $iterable[] = [$listener, 'process'];
            }
        }
        return $iterable;
    }

}
