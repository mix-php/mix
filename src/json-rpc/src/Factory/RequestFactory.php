<?php

namespace Mix\JsonRpc\Factory;

use Mix\Context\Context;
use Mix\JsonRpc\Message\Request;

/**
 * Class RequestFactory
 * @package Mix\JsonRpc\Factory
 */
class RequestFactory
{

    /**
     * Create Request
     * @param string $method
     * @param array|object $params
     * @param null $id
     * @return Request
     */
    public function createRequest(string $method, $params, $id = null)
    {
        $request          = new Request();
        $request->jsonrpc = '2.0';
        $request->method  = $method;
        $request->params  = $params;
        $request->id      = $id;
        $request->context = new Context();
        return $request;
    }

}
