<?php

namespace Mix\Tracing\Zipkin;

use OpenTracing\NoopTracer;

/**
 * Class Zipkin
 * @package Mix\Tracing\Zipkin
 */
class Zipkin
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
     * Start tracer
     * @param string $serviceName
     * @param null $ip
     * @param null $port
     * @return \OpenTracing\Tracer
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function startTracer(string $serviceName, $ip = null, $port = null)
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

}
