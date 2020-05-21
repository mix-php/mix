<?php

namespace Mix\Micro;

use Mix\Micro\Config\ConfigInterface;
use Mix\Micro\Register\RegistryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Options
 * @package Mix\Micro
 */
class Options
{

    /**
     * Options constructor.
     * @param \Closure ...$options
     */
    public function __construct(\Closure ...$options)
    {
        foreach ($options as $option) {
            call_user_func($option, $this);
        }
    }

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string|null
     */
    public $version;

    /**
     * @var array
     */
    public $metadata = [];

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var RegistryInterface
     */
    public $registry;

    /**
     * @var ConfigInterface
     */
    public $config;

    /**
     * @var \Mix\Http\Server\Server|\Mix\Grpc\Server|\Mix\JsonRpc\Server
     */
    public $server;

    /**
     * @var \Mix\Micro\Route\Router
     */
    public $router;

    /**
     * @var bool 
     */
    public $signal = true;

}
