<?php

namespace Mix\WebSocket\Client;

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
     * Dial
     * @param string $url
     * @return Connection
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
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
