<?php

namespace Mix\Micro\Gateway;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Cookie\Cookie;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\Gateway\Proxy\ApiOrWebProxy;
use Mix\Micro\RegistryInterface;
use Mix\Micro\ServiceInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine\Http\Client;

/**
 * Class Handler
 * @package Mix\Micro\Gateway
 */
class Handler implements HandlerInterface
{

    /**
     * @var Namespaces
     */
    public $namespaces;

    /**
     * @var RegistryInterface
     */
    public $registry;

    /**
     * @var float
     */
    public $proxyTimeout = 5.0;

    /**
     * @var LoggerInterface
     */
    public $log;

    /**
     * @var string
     */
    public $logFormat = 'status} | {uri} | {service}';

    /**
     * Handler constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Handle http
     * @param ServerRequest $request
     * @param Response $response
     */
    public function handleHTTP(ServerRequest $request, Response $response)
    {
        $path = $request->getUri()->getPath();
        switch ($path) {
            case '/jsonrpc':
                break;
            default:
                (new ApiOrWebProxy())->proxy($this, $request, $response);
        }
    }

    /**
     * Print log
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log(string $level, string $message, array $context = [])
    {
        if (!isset($this->log)) {
            return;
        }
        $this->log->log($level, $message, $context);
    }

    /**
     * Get service
     *
     * Url                  Service        Method
     * /foo/bar             foo            Foo.Bar
     * /foo/bar/baz         foo            Bar.Baz
     * /foo/bar/baz/cat     foo.bar        Baz.Cat
     *
     * @param string $path
     * @param string $namespace
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function service(string $path, string $namespace)
    {
        $slice = array_filter(explode('/', $path));
        switch (count($slice)) {
            case 0:
            case 1:
                throw new NotFoundException('Invalid proxy path');
                break;
            case 2:
            case 3:
                $name = array_shift($slice);
                break;
            default:
                array_pop($slice);
                array_pop($slice);
                $name = implode('/', $slice);
        }
        return $this->registry->get(sprintf('%s.%s', $namespace, $name));
    }

    /**
     * 404 处理
     * @param \Throwable $e
     * @param Response $response
     */
    public static function show404(Response $response)
    {
        $content = '404 Not Found';
        $body    = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(404)
            ->end();
    }

    /**
     * 502 处理,
     * @param \Throwable $e
     * @param Response $response
     */
    public static function show502(Response $response)
    {
        $content = '502 Bad Gateway';
        $body    = (new StreamFactory())->createStream($content);
        return $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(502)
            ->end();
    }

    /**
     * Server shutdown, clear resource
     */
    public function clear()
    {
        $this->registry->clear();
    }

}
