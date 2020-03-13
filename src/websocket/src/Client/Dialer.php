<?php

namespace Mix\WebSocket\Client;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\ServerRequest;
use Mix\WebSocket\Exception\UpgradeException;

/**
 * Class Dialer
 * @package Mix\WebSocket\Client
 */
class Dialer
{

    /**
     * @var string[]
     */
    public $cookies = [];

    /**
     * @var float
     */
    public $timeout = 5.0;

    /**
     * Dialer constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Dial
     * @param string $url
     * @param array $headers
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws UpgradeException
     */
    public function dial(string $url, array $headers = [])
    {
        $conn = new Connection([
            'url'     => $url,
            'headers' => $headers,
            'cookies' => $this->cookies,
            'timeout' => $this->timeout,
        ]);
        $conn->connect();;
        return $conn;
    }

}
