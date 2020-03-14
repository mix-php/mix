<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Stream\FileStream;
use Mix\Micro\Gateway\Helper\ProxyHelper;
use Mix\Micro\Gateway\ProxyInterface;
use Mix\Micro\ServiceInterface;
use Mix\WebSocket\Upgrader;
use Psr\EventDispatcher\EventDispatcherInterface;
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
    public $pattern = '/';

    /**
     * @var string
     */
    public $namespace = 'php.micro.web';

    /**
     * @var float
     */
    public $timeout = 5.0;

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
     * Get namespace
     * @return string
     */
    public function namespace()
    {
        return $this->namespace;
    }

    /**
     * Proxy
     * @param ServiceInterface $service
     * @param ServerRequest $request
     * @param Response $response
     * @return bool
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response)
    {
        // websocket
        if (ProxyHelper::isWebSocket($request)) {
            $webSocketProxy             = new WebSocketProxy($this->upgrader, $this->timeout);
            $webSocketProxy->dispatcher = $this->dispatcher;
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
            return false;
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
