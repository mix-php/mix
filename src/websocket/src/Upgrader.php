<?php

namespace Mix\WebSocket;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\WebSocket\Exception\UpgradeException;

/**
 * Class Upgrader
 * @package Mix\WebSocket
 */
class Upgrader
{

    use ConnectionManager;

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return Connection
     * @throws UpgradeException
     */
    public function upgrade(ServerRequest $request, Response $response): Connection
    {
        // Handshake verification
        if ($request->getHeaderLine('connection') !== 'Upgrade' || $request->getHeaderLine('upgrade') !== 'websocket') {
            throw new UpgradeException('Handshake failed, invalid WebSocket request');
        }
        $secWebSocketKey = $request->getHeaderLine('sec-websocket-key');
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if ($request->getHeaderLine('sec-websocket-version') != 13 || 0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            throw new UpgradeException('Handshake failed, invalid WebSocket protocol v13');
        }

        // Upgrade
        $swooleResponse = $response->getRawResponse();
        if (!$swooleResponse || !$swooleResponse instanceof \Swoole\Http\Response) {
            throw new UpgradeException('Handshake failed, only the swoole coroutine environment is supported');
        }
        if (!$swooleResponse->upgrade()) {
            throw new UpgradeException('Handshake failed, upgrade error');
        }
        $response->withStatus(101);
        $connection = new Connection($swooleResponse, $this);
        $this->add($connection);
        return $connection;
    }

    /**
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     * @return Connection
     * @throws UpgradeException
     */
    public function upgradeRaw(\Swoole\Http\Request $request, \Swoole\Http\Response $response): Connection
    {
        // Handshake verification
        if (($request->header['connection'] ?? '') !== 'Upgrade' || ($request->header['upgrade'] ?? '') !== 'websocket') {
            throw new UpgradeException('Handshake failed, invalid WebSocket request');
        }
        $secWebSocketKey = $request->header['sec-websocket-key'] ?? '';
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (($request->header['sec-websocket-version'] ?? '') != 13 || 0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            throw new UpgradeException('Handshake failed, invalid WebSocket protocol v13');
        }

        // Upgrade
        if (!$response) {
            throw new UpgradeException('Handshake failed, only the swoole coroutine environment is supported');
        }
        if (!$response->upgrade()) {
            throw new UpgradeException('Handshake failed, upgrade error');
        }
        $response->status(101);
        $connection = new Connection($response, $this);
        $this->add($connection);
        return $connection;
    }

}
