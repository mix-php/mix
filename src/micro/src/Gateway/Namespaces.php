<?php

namespace Mix\Micro\Gateway;

use Mix\Bean\BeanInjector;

/**
 * Class Namespaces
 * @package Mix\Micro\Gateway
 */
class Namespaces
{
    
    /**
     * @var string
     */
    public $api = 'php.micro.api';

    /**
     * @var string
     */
    public $web = 'php.micro.web';

    /**
     * @var string
     */
    public $jsonrpc = 'php.micro.srv.jsonrpc';

    /**
     * @var string
     */
    public $grpc = 'php.micro.srv.grpc';

    /**
     * Namespaces constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

}
