<?php

namespace App\Http\Middleware;

use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CorsMiddleware
 * @package App\Http\Middleware
 * @author liu,jian <coder.keda@gmail.com>
 */
class CorsMiddleware implements MiddlewareInterface
{

    /**
     * @var ServerRequest
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * ActionMiddleware constructor.
     * @param ServerRequest $request
     * @param Response $response
     */
    public function __construct(ServerRequest $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO: Implement process() method.
        // 跨域处理
        $response = $this->response;
        if ($request->getMethod() == 'OPTIONS') {
            $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization')
                ->withBody((new StreamFactory())->createStream(''));
            return $response;
        }

        // 继续往下执行
        return $handler->handle($request);
    }

}
