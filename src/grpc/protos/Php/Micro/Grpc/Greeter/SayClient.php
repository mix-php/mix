<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Php\Micro\Grpc\Greeter;

/**
 */
class SayClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Php\Micro\Grpc\Greeter\Request $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Php\Micro\Grpc\Greeter\Response
     */
    public function Hello(\Php\Micro\Grpc\Greeter\Request $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/php.micro.grpc.greeter.Say/Hello',
        $argument,
        ['\Php\Micro\Grpc\Greeter\Response', 'decode'],
        $metadata, $options);
    }

}
