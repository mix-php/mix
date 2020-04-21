<?php

namespace Mix\Tracing\Zipkin;

use Mix\Bean\BeanInjector;
use Mix\Tracing\Zipkin\Exception\NotFoundException;
use Mix\Tracing\Zipkin\Exception\UnavailableException;
use OpenTracing\Exceptions\InvalidReferencesSet;
use OpenTracing\Exceptions\InvalidSpanOption;
use OpenTracing\Exceptions\UnsupportedFormat;
use OpenTracing\Propagators\TextMapReader;
use OpenTracing\Propagators\TextMapWriter;
use OpenTracing\Scope;
use OpenTracing\ScopeManager;
use OpenTracing\Span;
use OpenTracing\SpanContext;
use OpenTracing\SpanReference;
use OpenTracing\StartSpanOptions;
use OpenTracing\Tag;
use Zipkin\DefaultTracing;
use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Propagation\TraceContext;

/**
 * Class Tracer
 * @package Mix\Tracing\Zipkin
 */
class Tracer implements \OpenTracing\Tracer
{

    /**
     * @var string
     */
    public $url = 'http://127.0.0.1:9411/api/v2/spans';

    /**
     * @var int
     */
    public $timeout = 0;

    /**
     * @var float
     */
    public $rate = 1;

    /**
     * @var string
     */
    public $serviceName;

    /**
     * @var string|null
     */
    public $ipv4 = null;

    /**
     * @var string|null
     */
    public $ipv6 = null;

    /**
     * @var int|null
     */
    public $port = null;

    /**
     * @var DefaultTracing
     */
    protected $tracing;

    /**
     * @var \Zipkin\Tracer
     */
    protected $tracer;

    /**
     * @var \Zipkin\Span
     */
    protected $rootSpan;

    /**
     * Tracer constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Init
     */
    public function init()
    {
        $endpoint      = \Zipkin\Endpoint::create($this->serviceName, $this->ipv4, $this->ipv6, $this->port);
        $sampler       = \Zipkin\Samplers\PercentageSampler::create((float)$this->rate);
        $reporter      = new \Zipkin\Reporters\Http(\Mix\Tracing\Zipkin\Reporter\GuzzleFactory::create(), [
            'endpoint_url' => $this->url,
            "timeout"      => $this->timeout,
        ]);
        $this->tracing = \Zipkin\TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();
    }

    /**
     * Returns the current {@link ScopeManager}, which may be a noop but may not be null.
     *
     * @return ScopeManager
     */
    public function getScopeManager(): ScopeManager
    {
        throw new UnavailableException('Unavailable method');
    }

    /**
     * Returns the active {@link Span}. This is a shorthand for
     * Tracer::getScopeManager()->getActive()->getSpan(),
     * and null will be returned if {@link Scope#active()} is null.
     *
     * @return Span|null
     */
    public function getActiveSpan(): ?Span
    {
        throw new UnavailableException('Unavailable method');
    }

    /**
     * Starts a new span that is activated on a scope manager.
     *
     * It's also possible to not finish the {@see \OpenTracing\Span} when
     * {@see \OpenTracing\Scope} context expires:
     *
     *     $scope = $tracer->startActiveSpan('...', [
     *         'finish_span_on_close' => false,
     *     ]);
     *     $span = $scope->getSpan();
     *     try {
     *         $span->setTag(Tags\HTTP_METHOD, 'GET');
     *         // ...
     *     } finally {
     *         $scope->close();
     *     }
     *     // $span->finish() is not called as part of Scope deactivation as
     *     // finish_span_on_close is false
     *
     * @param string $operationName
     * @param array|StartSpanOptions $options Same as for startSpan() with
     *     aditional option of `finish_span_on_close` that enables finishing
     *     of span whenever a scope is closed. It is true by default.
     *
     * @return Scope A Scope that holds newly created Span and is activated on
     *               a ScopeManager.
     */
    public function startActiveSpan($operationName, $options = []): Scope
    {
        if (!$this->tracer || !$this->rootSpan) {
            throw new NotFoundException('Root span not found');
        }

        $subSpan = $this->tracer->newChild($this->rootSpan->getContext());
        $span    = new \Mix\Tracing\Zipkin\Span\Span($this, $subSpan, $operationName);

        if (!empty($options['tags'])) {
            foreach ($options['tags'] as $key => $val) {
                $span->setTag($key, $val);
            }
        }

        return new \Mix\Tracing\Zipkin\Scope\Scope($span);
    }

    /**
     * Starts and returns a new span representing a unit of work.
     *
     * Whenever `child_of` reference is not passed then
     * {@see \OpenTracing\ScopeManager::getActive()} span is used as `child_of`
     * reference. In order to ignore implicit parent span pass in
     * `ignore_active_span` option set to true.
     *
     * Starting a span with explicit parent:
     *
     *     $tracer->startSpan('...', [
     *         'child_of' => $parentSpan,
     *     ]);
     *
     * @param string $operationName
     * @param array|StartSpanOptions $options See StartSpanOptions for
     *                                        available options.
     *
     * @return Span
     *
     * @throws InvalidSpanOption for invalid option
     * @throws InvalidReferencesSet for invalid references set
     * @see \OpenTracing\StartSpanOptions
     *
     */
    public function startSpan($operationName, $options = []): Span
    {
        $tracing = $this->tracing;
        $tracer  = $this->tracer = $tracing->getTracer();
        if (!empty($options['child_of'])) {
            /** @var SpanContext $spanContext */
            $spanContext      = $options['child_of'];
            $extractor        = $tracing->getPropagation()->getExtractor(new \Zipkin\Propagation\Map());
            $extractedContext = $extractor($spanContext->getIterator());
            $rootSpan         = $this->rootSpan = $tracer->nextSpan($extractedContext);
        } else {
            $rootSpan = $this->rootSpan = $tracer->newTrace();
        }

        $span = new \Mix\Tracing\Zipkin\Span\Span($this, $rootSpan, $operationName);

        if (!empty($options['tags'])) {
            foreach ($options['tags'] as $key => $val) {
                $span->setTag($key, $val);
            }
        }

        return $span;
    }

    /**
     * @param SpanContext $spanContext
     * @param string $format
     * @param mixed $carrier
     *
     * @throws UnsupportedFormat when the format is not recognized by the tracer
     * implementation
     * @see Formats
     *
     */
    public function inject(SpanContext $spanContext, $format, &$carrier): void
    {
        $tracing  = $this->tracing;
        $metadata = [];
        $injector = $tracing->getPropagation()->getInjector(new \Zipkin\Propagation\Map());
        $injector($spanContext->context, $metadata);
        switch ($format) {
            case \OpenTracing\Formats\TEXT_MAP:
                $carrier = array_merge($carrier, $metadata);
                break;
            default:
                throw new UnsupportedFormat('Unavailable format');
        }
    }

    /**
     * @param string $format
     * @param mixed $carrier
     * @return SpanContext|null
     *
     * @throws UnsupportedFormat when the format is not recognized by the tracer
     * implementation
     * @see Formats
     *
     */
    public function extract($format, $carrier): ?SpanContext
    {
        $tracing = $this->tracing;
        switch ($format) {
            case \OpenTracing\Formats\TEXT_MAP:
                $extractor        = $tracing->getPropagation()->getExtractor(new \Zipkin\Propagation\Map());
                $extractedContext = $extractor($carrier);
                break;
            default:
                throw new UnsupportedFormat('Unavailable format');
        }
        if ($extractedContext instanceof DefaultSamplingFlags) {
            return null;
        }
        return new \Mix\Tracing\Zipkin\Span\SpanContext($this, $extractedContext);
    }

    /**
     * Allow tracer to send span data to be instrumented.
     *
     * This method might not be needed depending on the tracing implementation
     * but one should make sure this method is called after the request is delivered
     * to the client.
     *
     * As an implementor, a good idea would be to use {@see register_shutdown_function}
     * or {@see fastcgi_finish_request} in order to not to delay the end of the request
     * to the client.
     */
    public function flush(): void
    {
        $this->tracer and $this->tracer->flush();
    }

}
