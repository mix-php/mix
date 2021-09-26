<?php

namespace App\Service;

use App\Container\Logger;
use App\Handler\Hello;
use Mix\WebSocket\Connection;
use Mix\WebSocket\Exception\CloseFrameException;
use Mix\WebSocket\Exception\ReadMessageException;
use Swoole\Coroutine\Channel;

class Session
{

    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var Channel
     */
    protected $writeChan;

    /**
     * Session constructor.
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
        $this->writeChan = new Channel(10);
    }

    /**
     * @param string $data
     */
    public function send(string $data): void
    {
        $this->writeChan->push($data);
    }

    public function start(): void
    {
        // 接收消息
        go(function () {
            while (true) {
                try {
                    $frame = $this->conn->readMessage();
                } catch (\Throwable $ex) {
                    $this->stop();
                    // 忽略一些异常日志
                    if ($ex instanceof CloseFrameException) {
                        return;
                    }
                    if ($ex instanceof ReadMessageException && $ex->getMessage() == 'Connection reset by peer') {
                        return;
                    }
                    Logger::instance()->error(sprintf('ReadMessage: %s: %s', get_class($ex), $ex->getMessage()));
                    return;
                }
                $message = $frame->data;

                (new Hello($this))->index($message);
            }
        });

        // 发送消息
        go(function () {
            while (true) {
                $data = $this->writeChan->pop();
                if (!$data) {
                    return;
                }

                $frame = new \Swoole\WebSocket\Frame();
                $frame->data = $data;
                $frame->opcode = WEBSOCKET_OPCODE_TEXT; // or WEBSOCKET_OPCODE_BINARY
                try {
                    $this->conn->writeMessage($frame);
                } catch (\Throwable $ex) {
                    Logger::instance()->error(sprintf('WriteMessage: %s', $ex->getMessage()));
                    $this->stop();
                    return;
                }
            }
        });
    }

    public function stop()
    {
        $this->writeChan->close();
    }

}
