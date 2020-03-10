<?php

namespace Mix\WebSocket\Client;

use Mix\Bean\BeanInjector;
use Mix\WebSocket\Exception\UpgradeException;

/**
 * Class Dialer
 * @package Mix\WebSocket\Client
 */
class Dialer
{

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
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws UpgradeException
     */
    public function dial(string $url)
    {
        $conn = new Connection([
            'url'     => $url,
            'timeout' => $this->timeout,
        ]);
        $conn->connect();;
        return $conn;
    }

}
