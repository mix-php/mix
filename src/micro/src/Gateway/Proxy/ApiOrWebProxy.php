<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Http\Message\Cookie\Cookie;
use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Stream\FileStream;
use Mix\Micro\Exception\NotFoundException;
use Mix\Micro\Gateway\Handler;
use Mix\WebSocket\Exception\UpgradeException;
use Mix\WebSocket\Upgrader;
use Psr\Http\Message\UriInterface;
use Swoole\Coroutine\Http\Client;

/**
 * Class ApiOrWebProxy
 * @package Mix\Micro\Gateway\Proxy
 */
class ApiOrWebProxy
{

    /**
     * @var Upgrader
     */
    public $upgrader;

    /**
     * WebSocketProxy constructor.
     */
    public function __construct()
    {
        $this->upgrader = new Upgrader();
    }

    /**
     * Proxy
     * @param Handler $handler
     * @param ServerRequest $request
     * @param Response $response
     */
    public function proxy(Handler $handler, ServerRequest $request, Response $response)
    {
        $service = $type = null;
        foreach ([$handler->namespaces->api, $handler->namespaces->web] as $key => $namespace) {
            try {
                $service = $handler->service($request->getUri()->getPath(), $namespace);
                $type    = ($key == 0) ? 'api' : 'web';
                break;
            } catch (NotFoundException $ex) {
            }
        }
        if (is_null($service)) {
            $handler::show404($response);
//            $handler->log('warning', [
//                'type'   => $type,
//                'status' => 404,
//                'method' => $request->getMethod(),
//                'uri'    => $request->getUri()->__toString(),
//            ]);
            return;
        }

        // websocket
        if (WebSocketProxy::isWebSocket($request)) {
            (new WebSocketProxy())->proxy($this->upgrader, $service, $handler, $request, $response);
            return;
        }

        // http
        $address = $service->getAddress();
        $port    = $service->getPort();
        $client  = static::createClient($address, $port);
        $client->set(['timeout' => $handler->proxyTimeout]);
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

        $path = static::getQueryPath($request->getUri());
        if (!$client->execute($path)) {
            $handler::show502($response);
            $this->log('warning', $this->logFormat, [
                'type'    => $type,
                'status'  => 502,
                'method'  => $request->getMethod(),
                'uri'     => $request->getUri()->__toString(),
                'service' => json_encode($service),
            ]);
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
            $response->withCookie(static::parseCookie($value));
        }
        $response
            ->withStatus($client->getStatusCode())
            ->withBody($body)
            ->end();

        $handler->log('info', [
            'type'    => $type,
            'status'  => $status,
            'method'  => $request->getMethod(),
            'uri'     => $request->getUri()->__toString(),
            'service' => json_encode($service),
        ]);
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

}
