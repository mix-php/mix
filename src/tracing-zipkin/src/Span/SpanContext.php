<?php

namespace Mix\Tracing\Zipkin\Span;

use Mix\Tracing\Zipkin\Tracer;
use Traversable;
use Zipkin\Propagation\TraceContext;

/**
 * Class SpanContext
 * @package Mix\Tracing\Zipkin\Span
 */
class SpanContext implements \OpenTracing\SpanContext
{

    /**
     * @var Tracer
     */
    public $tracer;

    /**
     * @var TraceContext
     */
    public $context;

    /**
     * @var string[]
     */
    public $baggage = [];

    /**
     * SpanContext constructor.
     * @param TraceContext $context
     */
    public function __construct(Tracer $tracer, TraceContext $context)
    {
        $this->tracer  = $tracer;
        $this->context = $context;
        $baggage       = [];
        $tracer->inject($this, \OpenTracing\Formats\TEXT_MAP, $baggage);
        $this->baggage = $baggage;
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->baggage;
    }

    /**
     * Returns the value of a baggage item based on its key. If there is no
     * value with such key it will return null.
     *
     * @param string $key
     * @return string|null
     */
    public function getBaggageItem($key): ?string
    {
        return $this->baggage[$key] ?? null;
    }

    /**
     * Creates a new SpanContext out of the existing one and the new key => value pair.
     *
     * @param string $key
     * @param string $value
     * @return \OpenTracing\SpanContext
     */
    public function withBaggageItem($key, $value): ?\OpenTracing\SpanContext
    {
        $this->baggage[$key] = $value;
        return $this;
    }

}