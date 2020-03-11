<?php

namespace Mix\WebSocket;

use Mix\WebSocket\Exception\CloseFrameException;
use Mix\WebSocket\Exception\ReceiveException;

/**
 * Class Connection
 * @package Mix\WebSocket
 * @author liu,jian <coder.keda@gmail.com>
 */
class Connection
{

    /**
     * @var \Swoole\Http\Response
     */
    public $swooleResponse;

    /**
     * @var ConnectionManager
     */
    public $connectionManager;

    /**
     * Connection constructor.
     * @param \Swoole\Http\Response $response
     * @param ConnectionManager $connectionManager
     */
    public function __construct(\Swoole\Http\Response $response, ConnectionManager $connectionManager)
    {
        $this->swooleResponse    = $response;
        $this->connectionManager = $connectionManager;
    }

    /**
     * Recv
     * @return \Swoole\WebSocket\Frame
     * @throws \Swoole\Exception
     */
    public function recv()
    {
        $frame = $this->swooleResponse->recv();
        if ($frame === false) { // 接收失败
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = swoole_last_error();
            $errMsg  = swoole_strerror($errCode, 9);
            throw new ReceiveException($errMsg, $errCode);
        }
        if ($frame instanceof \Swoole\WebSocket\CloseFrame) { // CloseFrame
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = $frame->code;
            $errMsg  = $frame->reason;
            throw new CloseFrameException($errMsg, $errCode);
        }
        if ($frame === "") { // 连接关闭
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = stripos(PHP_OS, 'Darwin') !== false ? 54 : 104; // mac=54, linux=104
            $errMsg  = swoole_strerror($errCode, 9);
            throw new ReceiveException($errMsg, $errCode);
        }
        return $frame;
    }

    /**
     * Send
     * @param \Swoole\WebSocket\Frame $data
     * @throws \Swoole\Exception
     */
    public function send(\Swoole\WebSocket\Frame $data)
    {
        $result = $this->swooleResponse->push($data);
        if ($result === false) {
            $socket = $this->swooleResponse->socket;
            throw new \Swoole\Exception($socket->errMsg ?: 'Send frame failed', $socket->errCode);
        }
    }

    /**
     * Close
     * @throws \Swoole\Exception
     */
    public function close()
    {
        $this->connectionManager->remove($this);
        // Swoole >= 4.4.8 才支持 close
        // 但在 4.4.13 ~ 4.4.14 当 server->shutdown执行后/response->recv失败后再 close 会抛出 http response is unavailable 致命错误，需升级 Swoole 版本
        if ($this->swooleResponse->close()) {
            return;
        }
        $socket  = $this->swooleResponse->socket;
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
