<?php

namespace Mix\WebSocket;

use Mix\WebSocket\Exception\CloseFrameException;
use Mix\WebSocket\Exception\ReadMessageException;
use Mix\WebSocket\Exception\WriteMessageException;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

/**
 * Class Connection
 * @package Mix\WebSocket
 */
class Connection
{

    /**
     * @var Response
     */
    public $swooleResponse;

    /**
     * @var Upgrader
     */
    public $upgrader;

    /**
     * @var bool
     */
    protected $receiving = false;

    /**
     * Connection constructor.
     * @param Response $response
     * @param Upgrader $upgrader
     */
    public function __construct(Response $response, Upgrader $upgrader)
    {
        $this->swooleResponse = $response;
        $this->upgrader = $upgrader;
    }

    /**
     * @param float $timeout
     * @return Frame
     * @throws ReadMessageException
     * @throws CloseFrameException
     */
    public function readMessage(float $timeout = -1): Frame
    {
        $this->receiving = true;
        $frame = $this->swooleResponse->recv($timeout);
        $this->receiving = false;
        if (!$frame) { // 接收失败
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            if ($frame === '') {
                throw new ReadMessageException('Connection is closed');
            }
            $errCode = swoole_last_error();
            if ($errCode != 0) {
                $errMsg = swoole_strerror($errCode, 9);
                throw new ReadMessageException($errMsg, $errCode);
            }
        }
        if ($frame instanceof \Swoole\WebSocket\CloseFrame) { // CloseFrame
            $this->close(); // 需要移除管理器内的连接，所以还要 close
            $errCode = $frame->code;
            $errMsg = $frame->reason;
            throw new CloseFrameException($errMsg, $errCode);
        }
        return $frame;
    }

    /**
     * @param Frame $data
     * @throws WriteMessageException
     */
    public function writeMessage(Frame $data): void
    {
        $result = $this->swooleResponse->push($data);
        if ($result === false) {
            $socket = $this->swooleResponse->socket;
            $errCode = $socket->errCode;
            if ($errCode != 0) {
                $errMsg = socket_strerror($errCode);
                throw new WriteMessageException($errMsg, $errCode);
            }
        }
    }

    /**
     * @throws \Swoole\Exception
     */
    public function close(): void
    {
        $this->upgrader->remove($this);

        // 丢弃socket缓冲区的消息，避免 ngx 抛出 104: Connection reset by peer
        // 避免出现: Uncaught Swoole\Error: Socket#7 has already been bound to another coroutine#7, reading of the same socket in coroutine#8 at the same time is not allowed
        if (!$this->receiving) {
            $limit = 10;
            while ($limit-- && $this->swooleResponse->recv(0.01)) {
            }
        }

        // Swoole >= 4.4.8 才支持 close
        // 但在 4.4.13 ~ 4.4.14 当 server->shutdown 执行后或者 response->recv 失败后再 close 会抛出 http response is unavailable 致命错误
        // 即使 try 忽略异常，在 4.4.13 ~ 4.4.14 server->shutdown 时依然是无法 close 连接的，需升级 Swoole 版本
        if ($this->swooleResponse->close()) {
            return;
        }

        $socket = $this->swooleResponse->socket;
        $errMsg = $socket->errMsg;
        $errCode = $socket->errCode;
        if ($errMsg == '' && $errCode == 0) {
            return;
        }
        if ($errMsg == 'Connection reset by peer') {
            return;
        }
        throw new \Swoole\Exception($errMsg, $errCode);
    }

}
