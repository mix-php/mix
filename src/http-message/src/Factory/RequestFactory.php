<?php

namespace Mix\Http\Message\Factory;

use Mix\Http\Message\Request;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Class RequestFactory
 * @package Mix\Http\Message\Factory
 * @author liu,jian <coder.keda@gmail.com>
 */
class RequestFactory implements RequestFactoryInterface
{

    /**
     * Create a new request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request. If
     *     the value is a string, the factory MUST create a UriInterface
     *     instance based on it.
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        if (is_string($uri)) {
            $uri = (new UriFactory())->createUri($uri);
        }
        return new Request($method, $uri);
    }

    /**
     * Create a new request.
     *
     * @param \Swoole\Http\Request $req
     * @return RequestInterface
     */
    public function createRequestFromSwoole(\Swoole\Http\Request $req): RequestInterface
    {
        list($scheme, $protocolVersion) = explode('/', $req->server['server_protocol']);
        $method      = $req->server['request_method'] ?? '';
        $scheme      = strtolower($scheme);
        $host        = $req->header['host'] ?? '';
        $requestUri  = $req->server['request_uri'] ?? '';
        $queryString = $req->server['query_string'] ?? '';
        $uri         = $scheme . '://' . $host . $requestUri . ($queryString ? "?{$queryString}" : '');

        $request = $this->createRequest($method, $uri);
        $request->withProtocolVersion($protocolVersion);
        $request->withRequestTarget($uri);

        $headers = $req->header ?? [];
        foreach ($headers as $name => $value) {
            $request->withHeader($name, $value);
        }

        $contentType = $serverRequest->getHeaderLine('content-type');
        $content     = '';
        if (strpos($contentType, 'multipart/form-data') === false) {
            $content = $req->rawContent();
        }
        $body = (new StreamFactory())->createStream($content);
        $serverRequest->withBody($body);

        return $request;
    }

}
