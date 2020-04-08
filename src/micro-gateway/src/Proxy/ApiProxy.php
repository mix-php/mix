<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;

/**
 * Class ApiProxy
 * @package Mix\Micro\Gateway\Proxy
 */
class ApiProxy extends WebProxy
{

    /**
     * @var string
     */
    public $namespace = 'php.micro.api';

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return void
     */
    public function show404(\Throwable $exception, Response $response)
    {
        $content = [
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'status'  => '404 Not Found',
        ];
        $body    = (new StreamFactory())->createStream(json_encode($content));
        $response
            ->withContentType('application/json', 'utf-8')
            ->withBody($body)
            ->withStatus(404)
            ->end();
    }

    /**
     * 500 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return void
     */
    public function show500(\Throwable $exception, Response $response)
    {
        $content = [
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'status'  => '500 Internal Server Error',
        ];
        $body    = (new StreamFactory())->createStream(json_encode($content));
        $response
            ->withContentType('application/json', 'utf-8')
            ->withBody($body)
            ->withStatus(500)
            ->end();
    }

}
