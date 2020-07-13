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
        /*重组事件的 数组格式 event为key listeners组成的索引数组为value*/
        $tmpListenerEvents = [];
        foreach ($listeners as $listener){
            $events = $listener->events();
            foreach ($events as $event) {
                if(array_key_exists($event, $tmpListenerEvents)){
                    array_push($tmpListenerEvents[$event], $listener);
                    continue;
                }
                $tmpListenerEvents[$event][] = $listener;
            }
        }
        $this->listeners = $tmpListenerEvents;
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
        $listeners = $this->listeners[$class];
        $iterable = [];
        foreach ($listeners as $listener) {
            $iterable[] = [$listener, 'process'];
        }
        return $iterable;
    }

}
