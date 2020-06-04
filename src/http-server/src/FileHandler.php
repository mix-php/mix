<?php

namespace Mix\Http\Server;

use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\Exception\NotFoundException;

/**
 * Class FileHandler
 * @package Mix\Http\Server
 */
class FileHandler implements ServerHandlerInterface
{

    /**
     * @var string
     */
    protected $dir;

    /**
     * FileHandler constructor.
     * @param string $dir
     */
    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param Response $response
     */
    public function handleHTTP(ServerRequest $request, Response $response)
    {
        $path = $request->getUri()->getPath();
        $file = sprintf('%s%s', $this->dir, $path);

        if (!file_exists($file)) {
            $this->error404(new NotFoundException('Not Found (#404)'), $response)->send();
            return;
        }

        // 防止相对路径攻击
        // 如：/static/../../foo.php
        $realpath = (string)realpath($file);
        if ($this->dir !== substr($realpath, 0, strlen($this->dir))) {
            $this->error404(new NotFoundException('Not Found (#404)'), $response)->send();
            return;
        }

        $response->getSwooleResponse()->sendfile($file);
    }

    /**
     * Handle HTTP
     * @param ServerRequest $request
     * @param Response $response
     */
    public function __invoke(ServerRequest $request, Response $response)
    {
        $this->handleHTTP($request, $response);
    }

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return Response
     */
    public function error404(\Throwable $exception, Response $response): Response
    {
        $content = '404 Not Found';
        $body    = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(404);
    }

}
