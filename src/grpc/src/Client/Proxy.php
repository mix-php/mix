<?php

namespace Mix\Grpc\Client;

use Grpc\ChannelCredentials;
use Mix\Bean\BeanInjector;
use Mix\Grpc\Exception\InvokeException;
use Mix\Grpc\Client\Middleware\MiddlewareDispatcher;

/**
 * Class Proxy
 * @package Mix\Grpc\Client
 */
class Proxy
{

    /**
     * @var \Grpc\BaseStub
     */
    public $client;

    /**
     * Global timeout
     * @var float
     */
    public $timeout = 0.0;

    /**
     * @var array MiddlewareInterface class or object
     */
    public $middleware = [];

    /**
     * Connection constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Call
     * @param $name
     * @param $arguments
     * @return object
     * @throws InvokeException
     */
    public function __call($name, $arguments)
    {
        $parameters           = new Parameters();
        $parameters->request  = $arguments[0] ?? null;
        $parameters->metadata = $arguments[1] ?? [];
        $parameters->options  = $arguments[2] ?? [];
        $callback             = [$this->client, $name];

        isset($parameters->options['timeout']) or $parameters->options['timeout'] = $this->timeout;

        $process = function (Parameters $parameters) use ($callback) {
            $object = call_user_func_array($callback, $parameters);
            list($reply, $status) = $object->wait();
            if (is_null($reply)) {
                throw new InvokeException($status->details, $status->code);
            }
            return $reply;
        };

        $interceptDispatcher = new MiddlewareDispatcher($this->middleware, $process, $parameters);
        return $interceptDispatcher->dispatch();
    }

    /**
     * Close
     */
    public function close()
    {
        $this->client and $this->client->close();
    }

}
