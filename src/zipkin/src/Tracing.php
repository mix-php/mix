<?php

namespace Mix\Zipkin;

use Mix\Zipkin\Exception\NotFoundException;
use OpenTracing\NoopTracer;

/**
 * Class Tracing
 * @package Mix\Zipkin
 */
class Tracing
{

    /**
     * @var string
     */
    public $url = 'http://127.0.0.1:9411/api/v2/spans';

    /**
     * @var bool
     */
    public $disable = false;

    /**
     * @var float
     */
    public $rate = 1;

    /**
     * @var int
     */
    public $timeout = 5;

    /**
     * Trace
     * @param string $serviceName
     * @param null $ip
     * @param null $port
     * @return \OpenTracing\Tracer
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function trace(string $serviceName, $ip = null, $port = null)
    {
        if ($this->disable) {
            return new NoopTracer();
        }
        $tracer = new Tracer([
            'url'         => $this->url,
            'timeout'     => $this->timeout,
            'rate'        => $this->rate,
            'serviceName' => $serviceName,
            'ipv4'        => strpos($ip, '.') !== false ? $ip : null,
            'ipv6'        => strpos($ip, ':') !== false ? $ip : null,
            'port'        => $port,
        ]);
        $tracer->init();
        return $tracer;
    }

    /**
     * 从上下文提取 Tracer
     * @param \ArrayObject $context
     * @return \OpenTracing\Tracer
     * @throws NotFoundException
     */
    public static function extract(\ArrayObject $context)
    {
        if (!isset($context['tracer'])) {
            throw new NotFoundException('Tracer not found, please use TracingServerMiddleware::class');
        }
        return $context['tracer'];
    }

}
