<?php

namespace Mix\WebSocket;

use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;
use Mix\WebSocket\Exception\UpgradeException;

/**
 * Class Upgrader
 * @package Mix\WebSocket
 * @author liu,jian <coder.keda@gmail.com>
 */
class Upgrader
{

    /**
     * @var ConnectionManager
     */
    public $connectionManager;

    /**
     * Upgrader constructor.
     */
    public function __construct()
    {
        $this->connectionManager = new ConnectionManager();
    }

    /**
     * Upgrade
     * @param ServerRequest $request
     * @param Response $response
     * @return Connection
     * @throws UpgradeException
     */
    public function Upgrade(ServerRequest $request, Response $response)
    {
        // Handshake verification
        if ($request->getHeaderLine('connection') !== 'Upgrade' || $request->getHeaderLine('upgrade') !== 'websocket') {
            throw new UpgradeException('Handshake failed, invalid WebSocket request');
        }
        $secWebSocketKey = $request->getHeaderLine('sec-websocket-key');
        $patten          = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if ($request->getHeaderLine('sec-websocket-version') != 13 || 0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            throw new UpgradeException('Handshake failed, invalid WebSocket protocol v13');
        }
        // Upgrade
        $swooleResponse = $response->getSwooleResponse();
        if (!$swooleResponse->upgrade()) {
            throw new UpgradeException('Handshake failed, upgrade error');
        }
        $response->withStatus(101);
        $connection = new Connection($swooleResponse, $this->connectionManager);
        $this->connectionManager->add($connection);
        return $connection;
    }

    /**
     * Destroy
     * @throws \Swoole\Exception
     */
    public function destroy()
    {
        $this->connectionManager->closeAll();
    }

}
