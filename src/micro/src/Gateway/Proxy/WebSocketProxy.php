<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Micro\Gateway\Handler;
use Mix\Micro\ServiceInterface;
use Mix\WebSocket\Client\Connection;
use Mix\WebSocket\Client\Dialer;
use Mix\WebSocket\Exception\CloseFrameException;
use Mix\WebSocket\Exception\ReceiveException;
use Mix\WebSocket\Upgrader;
use Psr\Http\Message\UriInterface;
use Swoole\Coroutine\Channel;

/**
 * Class WebSocketProxy
 * @package Mix\Micro\Gateway\Proxy
 */
class WebSocketProxy
{

    /**
     * @var Connection
     */
    protected $serviceConn;

    /**
     * @var Connection
     */
    protected $clientConn;

    /**
     * Proxy
     * @param Upgrader $upgrader
     * @param ServiceInterface $service
     * @param Handler $handler
     * @param ServerRequest $request
     * @param Response $response
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function proxy(Upgrader $upgrader, ServiceInterface $service, Handler $handler, ServerRequest $request, Response $response)
    {
        $address = $service->getAddress();
        $port    = $service->getPort();
        $path    = static::getQueryPath($request->getUri());
        $headers = [];
        foreach ($request->getHeaders() as $name => $header) {
            $headers[$name] = implode(',', $header);
        }
        $cookies = $request->getCookieParams();
        $dialer  = new Dialer([
            'cookies' => $cookies,
            'timeout' => 5.0,
        ]);
        try {
            $this->serviceConn = $dialer->dial(sprintf('ws://%s:%d%s', $address, $port, $path), $headers);
            $this->clientConn  = $upgrader->Upgrade($request, $response);
        } catch (\Throwable $ex) {

            var_dump($ex->getMessage());

            $response
                ->withBody((new StreamFactory())->createStream('401 Unauthorized'))
                ->withStatus(401)
                ->end();
            return;
        }
        xgo([$this, 'clientRecv']);
        xgo([$this, 'serviceRecv']);
    }

    /**
     * Client recv
     * @throws \Throwable
     */
    public function clientRecv()
    {
        while (true) {
            try {
                $frame = $this->clientConn->recv();
                $this->serviceConn->send($frame);
            } catch (\Throwable $e) {
                // 对方发送CloseFrame
                if ($e instanceof CloseFrameException) {
                    try {
                        $frame         = new \Swoole\WebSocket\CloseFrame();
                        $frame->code   = $e->getCode();
                        $frame->reason = $e->getMessage();
                        $this->serviceConn->send($frame);
                    } catch (\Throwable $none) {
                    }
                    $this->close();
                    return;
                }
                // 发送失败
                $this->close();
            }
        }
    }

    /**
     * Service recv
     * @throws \Throwable
     */
    public function serviceRecv()
    {
        while (true) {
            try {
                $frame = $this->serviceConn->recv();
                $this->clientConn->send($frame);
            } catch (\Throwable $e) {
                // 对方发送CloseFrame
                if ($e instanceof CloseFrameException) {
                    try {
                        $frame         = new \Swoole\WebSocket\CloseFrame();
                        $frame->code   = $e->getCode();
                        $frame->reason = $e->getMessage();
                        $this->serviceConn->send($frame);
                    } catch (\Throwable $ee) {
                    }
                    $this->close();
                    return;
                }
                // 发送失败
                $this->close();
            }
        }
    }

    /**
     * Close
     * @throws \Swoole\Exception
     */
    protected function close()
    {
        $this->clientConn->close();
        $this->serviceConn->close();
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
     * 判断是否为 websocket
     * @return bool
     */
    public static function isWebSocket(ServerRequest $request)
    {
        if ($request->getHeaderLine('connection') !== 'Upgrade' || $request->getHeaderLine('upgrade') !== 'websocket') {
            return false;
        }
        return true;
    }

}
