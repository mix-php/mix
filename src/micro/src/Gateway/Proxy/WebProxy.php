<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Stream\FileStream;
use Mix\Micro\Gateway\Exception\ProxyException;
use Mix\Micro\Register\Exception\NotFoundException;
use Mix\Micro\Gateway\Helper\ProxyHelper;
use Mix\Micro\Gateway\ProxyInterface;
use Mix\Micro\Register\RegistryInterface;
use Mix\Micro\Register\ServiceInterface;
use Mix\WebSocket\Upgrader;
use Swoole\Coroutine\Http\Client;

/**
 * Class WebProxy
 * @package Mix\Micro\Gateway\Proxy
 */
class WebProxy implements ProxyInterface
{

    /**
     * @var string
     */
    public $namespace = 'php.micro.web';

    /**
     * @var float
     */
    public $timeout = 5.0;

    /**
     * @var string
     */
    protected $pattern = '/';

    /**
     * @var Upgrader
     */
    protected $upgrader;

    /**
     * WebOrApiProxy constructor.
     * @param array $config
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
        $this->upgrader = new Upgrader();
    }

    /**
     * Get handle pattern
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * Get service
     *
     * Url                  Service
     * /                    index
     * /foo                 foo
     * /foo/bar             foo
     * /foo/bar/baz         foo
     * /foo/bar/baz/cat     foo.bar
     * /v1/foo/bar          v1.foo
     * /v1/foo/bar/baz      v1.foo
     * /v1/foo/bar/baz/cat  v1.foo.bar
     *
     * @param RegistryInterface $registry
     * @param ServerRequest $request
     * @return ServiceInterface
     * @throws NotFoundException
     */
    public function service(RegistryInterface $registry, ServerRequest $request)
    {
        $path    = $request->getUri()->getPath();
        $slice   = array_filter(explode('/', $path));
        $version = '';
        if (isset($slice[1]) && stripos($slice[1], 'v') === 0) {
            $version = array_shift($slice) . '.';
        }
        switch (count($slice)) {
            case 0:
                $name = 'index';
                break;
            case 1:
            case 2:
            case 3:
                $name = array_shift($slice);
                break;
            default:
                array_pop($slice);
                array_pop($slice);
                $name = implode('/', $slice);
        }
        return $registry->service(sprintf('%s.%s', $this->namespace, $version . $name));
    }

    /**
     * Proxy
     * @param ServiceInterface $service
     * @param ServerRequest $request
     * @param Response $response
     * @return int status
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     * @throws ProxyException
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response)
    {
        // websocket
        if (ProxyHelper::isWebSocket($request)) {
            $webSocketProxy = new WebSocketProxy($this->upgrader, $this->timeout);
            return $webSocketProxy->proxy($service, $request, $response);
        }

        // http
        $address = $service->getAddress();
        $port    = $service->getPort();
        $client  = new Client($address, $port);
        $client->set(['timeout' => $this->timeout]);
        $client->setMethod($request->getMethod());

        $headers = [];
        foreach ($request->getHeaders() as $name => $header) {
            $headers[$name] = implode(',', $header);
        }
        $client->setHeaders($headers);
        $client->setCookies($request->getCookieParams());

        $files = $request->getUploadedFiles();
        if (!empty($files)) {
            $client->setData($request->getParsedBody());
            foreach ($files as $name => $file) {
                /** @var FileStream $stream */
                $stream = $file->getStream();
                $client->addFile($stream->getFilename(), $name, $file->getClientMediaType(), $file->getClientFilename());
            }
        } else {
            $client->setData($request->getBody()->getContents());
        }

        $requestUri = ProxyHelper::getRequestUri($request->getUri());
        if (!$client->execute($requestUri)) {
            throw new ProxyException($client->errMsg, $client->errCode);
        }

        $body   = (new StreamFactory())->createStream($client->getBody() ?: '');
        $status = $client->getStatusCode();
        foreach ($client->getHeaders() ?: [] as $key => $value) {
            if (in_array($key, ['content-length', 'content-encoding', 'set-cookie'])) {
                continue;
            }
            $response->withHeader($key, $value);
        }
        foreach ($client->set_cookie_headers ?: [] as $value) {
            $response->withCookie(ProxyHelper::parseCookie($value));
        }
        $response
            ->withStatus($status)
            ->withBody($body)
            ->end();
        return $status;
    }

    /**
     * 404 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return void
     */
    public function show404(\Throwable $exception, Response $response)
    {
        $content = '404 Not Found';
        $body    = (new StreamFactory())->createStream($content);
        $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(404)
            ->end();
    }

    /**
     * 500 处理
     * @param \Throwable $exception
     * @param Response $response
     * @return void
     */
    public function show500(\Throwable $exception, Response $response)
    {
        $content = '500 Internal Server Error';
        $body    = (new StreamFactory())->createStream($content);
        $response
            ->withContentType('text/plain')
            ->withBody($body)
            ->withStatus(500)
            ->end();
    }

    /**
     * Close
     * @throws \Swoole\Exception
     */
    public function close()
    {
        $this->upgrader->destroy();
    }

}
