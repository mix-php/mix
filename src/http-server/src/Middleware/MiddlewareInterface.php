<?php

namespace Mix\Http\Server\Middleware;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Interface MiddlewareInterface
 * @package Mix\Http\Server\Middleware
 * @author liu,jian <coder.keda@gmail.com>
 */
interface MiddlewareInterface extends \Psr\Http\Server\MiddlewareInterface
{

    /**
     * MiddlewareInterface constructor.
     * @param ServerRequest $request
     * @param Response $response
     */
    public function __construct(ServerRequest $request, Response $response);

}
