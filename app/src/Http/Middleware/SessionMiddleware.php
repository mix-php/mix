<?php

namespace App\Http\Middleware;

use App\Http\Helpers\SendHelper;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Middleware\MiddlewareInterface;
use Mix\Session\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class SessionMiddleware
 * @package App\Http\Middleware
 * @author liu,jian <coder.keda@gmail.com>
 */
class SessionMiddleware implements MiddlewareInterface
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
     * @var Session
     */
    public $session;

    /**
     * SessionMiddleware constructor.
     * @param ServerRequest $request
     * @param Response $response
     */
    public function __construct(ServerRequest $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->session  = context()->getBean('session', [
            'request'  => $request,
            'response' => $response,
        ]);
        $this->request->withSession($this->session); // 把Session放入Request，方便其他位置调用
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
        // 会话验证
        $payload = $this->session->get('payload');
        if (!$payload) {
            // 中断执行，返回错误信息
            $content  = ['code' => 100001, 'message' => 'No access'];
            $response = SendHelper::json($this->response, $content);
            return $response;
        }

        // 继续往下执行
        return $handler->handle($request);
    }

}
