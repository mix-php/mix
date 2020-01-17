<?php

namespace Mix\Redis\Subscribe;

use Swoole\Coroutine\Channel;

/**
 * Class CommandInvoker
 * @package Mix\Redis\Subscribe
 */
class CommandInvoker
{

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * EOF
     */
    const EOF = "\r\n";

    /**
     * @var Channel
     */
    protected $resultChannel;

    /**
     * @var Channel
     */
    protected $messageChannel;

    /**
     * CommandInvoker constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection     = $connection;
        $this->resultChannel  = new Channel();
        $this->messageChannel = new Channel(100);
        \Swoole\Coroutine::create([$this, 'receive'], $connection);
    }

    /**
     * Receive
     * @param Connection $connection
     */
    public function receive(Connection $connection)
    {
        $buffer = null;
        while (true) {
            $line = $connection->recv();
            if ($line === false || $line === "") {
                $this->interrupt();
                break;
            }
            $line = substr($line, 0, -(strlen(static::EOF)));

            if ($line == '+OK') {
                $this->resultChannel->push($line);
                continue;
            }

            if ($line == '*3') {
                if (!empty($buffer)) {
                    $this->resultChannel->push($buffer);
                    $buffer = null;
                }
                $buffer[] = $line;
                continue;
            }

            $buffer[] = $line;

            $type = $buffer[2] ?? false;

            if ($type == 'subscribe' && count($buffer) == 6) {
                $this->resultChannel->push($buffer);
                $buffer = null;
                continue;
            }

            if ($type == 'unsubscribe' && count($buffer) == 6) {
                $this->resultChannel->push($buffer);
                $buffer = null;
                continue;
            }

            if ($type == 'message' && count($buffer) == 7) {
                $message          = new Message();
                $message->channel = $buffer[4];
                $message->payload = $buffer[6];
                $this->messageChannel->push($message);
                $buffer = null;
                continue;
            }
        }
    }

    /**
     * Invoke
     * @param string $command
     * @param int $number
     * @return array
     */
    public function invoke(string $command, int $number)
    {
        try {
            $this->connection->send($command . static::EOF);
        } catch (\Throwable $e) {
            $this->interrupt();
            throw $e;
        }
        $result = [];
        for ($i = 0; $i < $number; $i++) {
            $result[] = $this->resultChannel->pop();
        }
        return $result;
    }

    /**
     * Channel
     * @return Channel
     */
    public function channel()
    {
        return $this->messageChannel;
    }

    /**
     * Interrupt
     * @return bool
     */
    public function interrupt()
    {
        $this->connection->close();
        $this->resultChannel->close();
        $this->messageChannel->close();
        return true;
    }

}
