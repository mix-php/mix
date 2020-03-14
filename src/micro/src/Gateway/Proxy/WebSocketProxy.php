<?php

namespace Mix\Micro\Gateway\Proxy;

use Mix\Http\Message\Factory\StreamFactory;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\Micro\Gateway\Helper\ProxyHelper;
use Mix\Micro\ServiceInterface;
use Mix\WebSocket\Client\Connection;
use Mix\WebSocket\Client\Dialer;
use Mix\WebSocket\Exception\CloseFrameException;
use Mix\WebSocket\Upgrader;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class WebSocketProxy
 * @package Mix\Micro\Gateway\Proxy
 */
class WebSocketProxy
{

    /**
     * @var Upgrader
     */
    public $upgrader;

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
     * @var Connection
     */
    protected $serviceConn;

    /**
     * @var Connection
     */
    protected $clientConn;

    /**
     * WebSocketProxy constructor.
     * @param Upgrader $upgrader
     * @param float $timeout
     */
    public function __construct(Upgrader $upgrader, float $timeout = 5.0)
    {
        $this->upgrader = $upgrader;
        $this->timeout  = $timeout;
    }

    /**
     * Proxy
     * @param ServiceInterface $service
     * @param ServerRequest $request
     * @param Response $response
     * @throws \PhpDocReader\AnnotationException
     * @throws \ReflectionException
     */
    public function proxy(ServiceInterface $service, ServerRequest $request, Response $response)
    {
        $address = $service->getAddress();
        $port    = $service->getPort();
        $path    = ProxyHelper::path($request->getUri());
        $headers = [];
        foreach ($request->getHeaders() as $name => $header) {
            $headers[$name] = implode(',', $header);
        }
        $cookies = $request->getCookieParams();
        $dialer  = new Dialer([
            'cookies' => $cookies,
            'timeout' => $this->timeout,
        ]);
        try {
            $this->serviceConn = $dialer->dial(sprintf('ws://%s:%d%s', $address, $port, $path), $headers);
            $this->clientConn  = $this->upgrader->Upgrade($request, $response);
        } catch (\Throwable $ex) {
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
                return;
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
                return;
            }
        }
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
     * Close
     * @throws \Swoole\Exception
     */
    public function close()
    {
        $this->clientConn->close();
        $this->serviceConn->close();
    }

}
