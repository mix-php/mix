<?php

namespace Mix\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface HandlerInterface
 * @package Mix\Http\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
interface HandlerInterface
{

    /**
     * Handle HTTP
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function handleHTTP(ServerRequestInterface $request, ResponseInterface $response);

}
