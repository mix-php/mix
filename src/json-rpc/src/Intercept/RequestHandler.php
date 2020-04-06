<?php

namespace Mix\JsonRpc\Intercept;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;

/**
 * Class RequestHandler
 * @package Mix\JsonRpc\Intercept
 */
class RequestHandler 
{

    /**
     * @var InterceptorInterface[]
     */
    public $interceptors;

    /**
     * RequestHandler constructor.
     * @param array $interceptors
     */
    public function __construct(array $interceptors)
    {
        $this->interceptors = $interceptors;
    }

    /**
     * Handle
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handle(Request $request, Response $response): Response
    {
        $interceptor = array_shift($this->interceptors);
        if (!$interceptor) {
            return $response;
        }
        return $interceptor->process($request, $response, $this);
    }

}
