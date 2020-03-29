<?php

namespace Mix\JsonRpc\Factory;

use Mix\JsonRpc\Message\Error;
use Mix\JsonRpc\Message\Response;

/**
 * Class ResponseFactory
 * @package Mix\JsonRpc\Factory
 */
class ResponseFactory
{

    /**
     * Create result response
     * @param $result
     * @param null $id
     * @return Response
     */
    public function createResultResponse($result, $id = null)
    {
        $response          = new Response();
        $response->jsonrpc = '2.0';
        $response->result  = $result;
        $response->id      = $id;
        return $response;
    }

    /**
     * Create error response
     * @param int $code
     * @param string $message
     * @param null $id
     * @return Response
     */
    public function createErrorResponse(int $code, string $message, $id = null)
    {
        $error             = new Error();
        $error->code       = $code;
        $error->message    = $message;
        $response          = new Response();
        $response->jsonrpc = '2.0';
        $response->error   = $error;
        $response->id      = $id;
        return $response;
    }

    /**
     * Create notification response
     * @param string $method
     * @param $params
     * @return Response
     */
    public function createNotificationResponse(string $method, $params)
    {
        $response          = new Response();
        $response->jsonrpc = '2.0';
        $response->method  = $method;
        $response->params  = $params;
        $response->id      = null;
        return $response;
    }

}
