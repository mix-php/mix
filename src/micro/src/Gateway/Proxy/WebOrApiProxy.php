<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Stream\FileStream;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\Gateway\Helper\ProxyHelper;
use Mix\Micro\ServiceInterface;
use Mix\WebSocket\Upgrader;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\Http\Client;

/**
 * Class WebOrApiProxy
 * @package Mix\Micro\Gateway\Proxy
 */
class WebOrApiProxy
{

    /**
     * Web代理的服务名称空间
     * @var string
     */
    public $web = 'php.micro.web';

    /**
     * Api代理的服务名称空间
     * @var string
     */
    public $api = 'php.micro.api';

    /**
     * @var float
     */
    public $timeout = 5.0;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

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
     * Proxy
     * @param ServerRequest $request
     * @param Response $response
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function proxy(ServerRequest $request, Response $response)
    {
        $service = $type = null;
        foreach ([$this->web, $this->api] as $key => $namespace) {
            try {
                $service = $this->service($request->getUri()->getPath(), $namespace);
                $type    = ($key == 0) ? 'api' : 'web';
                break;
            } catch (NotFoundException $ex) {
            }
        }
        if (is_null($service)) {
            static::show404($response);

            return;
        }

        // websocket
        if (ProxyHelper::isWebSocket($request)) {
            $webSocketProxy             = new WebSocketProxy($this->upgrader, $this->timeout);
            $webSocketProxy->dispatcher = $this->dispatcher;
            $webSocketProxy->proxy($service, $request, $response);
            return;
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

        $path = ProxyHelper::path($request->getUri());
        if (!$client->execute($path)) {
            static::show502($response);

            return;
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
            ->withStatus($client->getStatusCode())
            ->withBody($body)
            ->end();


    }

    /**
     * Dispatch
     * @param object $event
     */
    protected function dispatch(object $event)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $this->dispatcher->dispatch($event);
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
    protected function service(string $path, string $namespace)
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
     * @param Response $response
     * @return void
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
     * @param Response $response
     * @return void
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
     * Close
     * @throws \Swoole\Exception
     */
    public function close()
    {
        $this->upgrader->destroy();
    }

}
