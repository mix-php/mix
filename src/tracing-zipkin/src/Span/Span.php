<?php

namespace Mix\Tracing\Zipkin\Span;

use Mix\Tracing\Zipkin\Exception\UnavailableException;
use Mix\Tracing\Zipkin\Tracer;
use OpenTracing\SpanContext;

/**
 * Class Span
 * @package Mix\Tracing\Zipkin\Span
 */
class Span implements \OpenTracing\Span
{

    /**
     * @var Tracer
     */
    public $tracer;

    /**
     * @var \Zipkin\Span
     */
    public $span;

    /**
     * @var string
     */
    public $operationName;

    /**
     * Span constructor.
     * @param \Zipkin\Span $span
     * @param string $operationName
     */
    public function __construct(Tracer $tracer, \Zipkin\Span $span, string $operationName)
    {
        $this->tracer        = $tracer;
        $this->span          = $span;
        $this->operationName = $operationName;
        $span->start();
        $span->setName($operationName);
    }

    /**
     * @return string
     */
    public function getOperationName(): string
    {
        return $this->operationName;
    }

    /**
     * Yields the SpanContext for this Span. Note that the return value of
     * Span::getContext() is still valid after a call to Span::finish(), as is
     * a call to Span::getContext() after a call to Span::finish().
     *
     * @return SpanContext
     */
    public function getContext(): SpanContext
    {
        return new \Mix\Tracing\Zipkin\Span\SpanContext($this->tracer, $this->span->getContext());
    }

    /**
     * Sets the end timestamp and finalizes Span state.
     *
     * With the exception of calls to getContext() (which are always allowed),
     * finish() must be the last call made to any span instance, and to do
     * otherwise leads to undefined behavior but not returning an exception.
     *
     * As an implementor, make sure you call {@see Tracer::deactivate()}
     * otherwise new spans might try to be child of this one.
     *
     * If the span is already finished, a warning should be logged.
     *
     * @param float|int|\DateTimeInterface|null $finishTime if passing float or int
     * it should represent the timestamp (including as many decimal places as you need)
     * @return void
     */
    public function finish($finishTime = null): void
    {
        $this->span->finish($finishTime);
    }

    /**
     * If the span is already finished, a warning should be logged.
     *
     * @param string $newOperationName
     */
    public function overwriteOperationName($newOperationName): void
    {
        $this->operationName = $newOperationName;
        $this->span->setName($newOperationName);
    }

    /**
     * Adds a tag to the span.
     *
     * If there is a pre-existing tag set for key, it is overwritten.
     *
     * As an implementor, consider using "standard tags" listed in {@see \OpenTracing\Tags}
     *
     * If the span is already finished, a warning should be logged.
     *
     * @param string $key
     * @param string|bool|int|float $value
     * @return void
     */
    public function setTag($key, $value): void
    {
        $this->span->tag($key, $value);
    }

    public function log(array $fields = [], $timestamp = null): void
    {
        throw new UnavailableException('Unavailable method');
    }

    public function addBaggageItem($key, $value): void
    {
        throw new UnavailableException('Unavailable method');
    }

    public function getBaggageItem($key): ?string
    {
        throw new UnavailableException('Unavailable method');
    }

}