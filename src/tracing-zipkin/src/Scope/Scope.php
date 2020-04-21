<?php

namespace Mix\Tracing\Zipkin\Scope;

use OpenTracing\Span;

/**
 * Class Scope
 * @package Mix\Tracing\Zipkin\Scope
 */
class Scope implements \OpenTracing\Scope
{

    /**
     * @var Span 
     */
    public $span;
    
    /**
     * Scope constructor.
     * @param Span $span
     */
    public function __construct(Span $span)
    {
        $this->span = $span;
    }

    /**
     * Mark the end of the active period for the current thread and {@link Scope},
     * updating the {@link ScopeManager#active()} in the process.
     *
     * NOTE: Calling {@link #close} more than once on a single {@link Scope} instance leads to undefined
     * behavior.
     */
    public function close(): void
    {
        $this->span->finish();
    }

    /**
     * @return Span the {@link Span} that's been scoped by this {@link Scope}
     */
    public function getSpan(): Span
    {
        return $this->span;
    }

}
