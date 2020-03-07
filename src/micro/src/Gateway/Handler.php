<?php

namespace Mix\Micro\Gateway;

use Mix\Bean\BeanInjector;
use Mix\Http\Message\Cookie\Cookie;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Server\HandlerInterface;
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
     * @var string
     */
    public $namespace = 'php.micro.api';

    /**
     * @var LoggerInterface
     */
    public $log;

    /**
     * @var RegistryInterface
     */
    public $registry;

    /**
     * @var float
     */
    public $timeout = 5.0;

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
            case '/json-rpc':
                $this->proxyJsonRpc($request, $response);
                break;
            default:
                $this->proxyAPI($request, $response);
        }
    }

    /**
     * Proxy API
     * @param ServerRequest $request
     * @param Response $response
     */
    protected function proxyAPI(ServerRequest $request, Response $response)
    {
        try {
            $service = $this->getService($request->getUri()->getPath());
        } catch (\Throwable $ex) {
            static::send($response, 404, '');
            return;
        }
        $address = $service->getAddress();
        $port    = $service->getPort();
        $client  = static::createClient($address, $port);
        $client->set(['timeout' => $this->timeout]);
        $client->setMethod($request->getMethod());
        $client->setData($request->getBody()->getContents());
        $client->setCookies($request->getCookieParams());
        $headers = [];
        foreach ($request->getHeaders() as $name => $header) {
            $headers[$name] = implode(',', $header);
        }
        $client->setHeaders($headers);
        $status = $client->execute(static::getQueryPath($request->getUri()));
        if (!$status) {
            static::send($response, 502, '');
            return;
        }
        static::send(
            $response,
            $client->getStatusCode(),
            $client->getBody() ?: '',
            $client->getHeaders() ?: [],
            $client->set_cookie_headers ?: []
        );
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
     * @return ServiceInterface
     * @throws \Exception
     */
    protected function getService(string $path)
    {
        $slice = array_filter(explode('/', $path));
        switch (count($slice)) {
            case 0:
            case 1:
                throw new \Exception('Invalid proxy path');
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
        return $this->registry->get(sprintf('%s.%s', $this->namespace, $name));
    }

    /**
     * Create client
     * @param string $address
     * @param int $port
     * @return Client
     */
    protected static function createClient(string $address, int $port)
    {
        return new Client($address, $port);
    }

    /**
     * Get query path
     * @param UriInterface $uri
     * @return string
     */
    protected static function getQueryPath(UriInterface $uri)
    {
        $path     = $uri->getPath();
        $query    = $uri->getQuery();
        $query    = $query ? "?{$query}" : '';
        $fragment = $uri->getFragment();
        $fragment = $fragment ? "#{$fragment}" : '';
        $full     = $path . $query . $fragment;
        return $full;
    }

    /**
     * Send
     * @param Response $response
     * @param int $status
     * @param string $content
     * @param array $headers
     * @param array $cookieHeaders
     */
    protected static function send(Response $response, int $status, string $content, array $headers = [], array $cookieHeaders = [])
    {
        $body = (new StreamFactory())->createStream($content);
        foreach ($headers as $key => $value) {
            if (in_array($key, ['content-length', 'content-encoding', 'set-cookie'])) {
                continue;
            }
            $response->withHeader($key, $value);
        }
        foreach ($cookieHeaders as $value) {
            $response->withCookie(static::parseCookie($value));
        }
        if (!isset($headers['content-type'])) {
            $response->withContentType('text/plain');
        }
        $response
            ->withStatus($status)
            ->withBody($body)
            ->end();
    }

    /**
     * parse Cookie
     * Parse cookie
     * @param string $header
     * @return Cookie
     */
    protected static function parseCookie(string $header)
    {
        $name     = '';
        $value    = '';
        $expire   = 0;
        $path     = '/';
        $domain   = '';
        $secure   = false;
        $httpOnly = false;
        foreach (explode('; ', $header) as $k => $v) {
            if ($k == 0) {
                list($name, $value) = explode('=', $v);
            }
            if (strpos($v, 'path=') === 0) {
                list(, $path) = explode('=', $v);
            }
            if (strpos($v, 'expires=') === 0) {
                list(, $gmt) = explode('=', $v);
                $expire = strtotime($gmt);
            }
            if (strpos($v, 'domain=') === 0) {
                list(, $domain) = explode('=', $v);
            }
            if ($v == 'secure') {
                $secure = true;
            }
            if ($v == 'httponly') {
                $httpOnly = true;
            }
        }
        return new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Proxy json-rpc
     * @param ServerRequest $request
     * @param Response $response
     */
    protected function proxyJsonRpc(ServerRequest $request, Response $response)
    {
    }

}
