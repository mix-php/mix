<?php

namespace Mix\Server;

use Mix\Server\Exception\ReceiveException;

/**
 * Class Connection
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class Connection
{

    /**
     * @var \Swoole\Coroutine\Server\Connection
     */
    protected $swooleConnection;

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var \Swoole\Coroutine\Socket
     */
    public $swooleSocket;

    /**
     * Connection constructor.
     * @param \Swoole\Coroutine\Server\Connection $connection
     * @param ConnectionManager $connectionManager
     */
    public function __construct(\Swoole\Coroutine\Server\Connection $connection, ConnectionManager $connectionManager)
    {
        $this->swooleConnection  = $connection;
        $this->connectionManager = $connectionManager;
        $this->swooleSocket      = method_exists($connection, 'exportSocket') ? $connection->exportSocket() : $connection->socket; // swoole >= 4.4.13 socket 修改成了 protected
    }

    /**
     * 获取远程地址
     * @return array
     */
    public function getRemoteAddress()
    {
        $address = $this->swooleSocket->getpeername();
        if ($address) {
            return $address;
        }
        return [
            'address' => '',
            'port'    => '',
        ];
    }

    /**
     * 获取本地地址
     * @return array
     */
    public function getLocalAddress()
    {
        $address = $this->swooleSocket->getsockname();
        if ($address) {
            return $address;
        }
        return [
            'address' => '',
            'port'    => '',
        ];
    }

    /**
     * Recv
     * @param float $timeout
     * @return string
     * @throws ReceiveException
     */
    public function recv(float $timeout = -1)
    {
        $data = $this->swooleConnection->recv($timeout);
        if ($data === false) { // 接收失败
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $socket = $this->swooleSocket;
            throw new ReceiveException($socket->errMsg, $socket->errCode);
        }
        if ($data === "") { // 连接关闭
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = stripos(PHP_OS, 'Darwin') !== false ? 54 : 104; // mac=54, linux=104
            $errMsg  = swoole_strerror($errCode, 9);
            throw new ReceiveException($errMsg, $errCode);
        }
        return $data;
    }

    /**
     * Send
     * @param string $data
     * @throws \Swoole\Exception
     */
    public function send(string $data)
    {
        $len  = strlen($data);
        $size = $this->swooleConnection->send($data);
        if ($size === false) {
            $socket = $this->swooleSocket;
            throw new \Swoole\Exception($socket->errMsg ?: 'Send data failed', $socket->errCode);
        }
        if ($len !== $size) {
            throw new \Swoole\Exception('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }
    }

    /**
     * Close
     * @throws \Swoole\Exception
     */
    public function close()
    {
        $this->connectionManager->remove($this);
        if ($this->swooleConnection->close()) {
            return;
        }
        $socket  = $this->swooleSocket;
        $errMsg  = $socket->errMsg;
        $errCode = $socket->errCode;
        if ($errMsg == '' && $errCode == 0) {
            return;
        }
        if ($errMsg == 'Connection reset by peer' && in_array($errCode, [54, 104])) { // mac=54, linux=104
            return;
        }
        throw new \Swoole\Exception($errMsg, $errCode);
    }

}
