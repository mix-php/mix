<?php

namespace Mix\Http\Message\Factory;

use Mix\Http\Message\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseFactory
 * @package Mix\Http\Message\Factory
 */
class ResponseFactory implements ResponseFactoryInterface
{

    /**
     * Create a new response.
     *
     * @param int $code HTTP status code; defaults to 200
     * @param string $reasonPhrase Reason phrase to associate with status code
     *     in generated response; if none is provided implementations MAY use
     *     the defaults as suggested in the HTTP specification.
     *
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response($code, $reasonPhrase);
    }

    /**
     * Create a new response.
     * @return ResponseInterface
     */
    public function createResponseFromFPM(): ResponseInterface
    {
        return $this->createResponse(200, '');
    }

    /**
     * Create a new ResponseInterface.
     *
     * @param \Swoole\Http\Response $response
     * @return ResponseInterface
     */
    public function createResponseFromSwoole(\Swoole\Http\Response $resp): ResponseInterface
    {
        /** @var Response $response */
        $response = $this->createResponse();
        $response->withRawResponse($resp);
        return $response;
    }

    public function createResponseFromSwow(\Swow\Http\Server\Connection $resp): ResponseInterface
    {
        /** @var Response $response */
        $response = $this->createResponse();
        $response->withRawResponse($resp);
        return $response;
    }

    /**
     * Create a new ResponseInterface.
     *
     * @param \Workerman\Connection\TcpConnection $conn
     * @return ResponseInterface
     */
    public function createResponseFromWorkerMan(\Workerman\Connection\TcpConnection $conn): ResponseInterface
    {
        /** @var Response $response */
        $response = $this->createResponse();
        $response->withRawResponse($conn);
        return $response;
    }

}
