<?php

namespace Mix\Redis\Subscribe;

use Swoole\Timer;
use Swoole\Coroutine;
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
        Coroutine::create(function () use ($connection) {
            $this->receive($connection);
        });
    }

    /**
     * Receive
     * @param Connection $connection
     * @throws \Swoole\Exception
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
                $timerID          = Timer::after(30 * 1000, function () use ($message) {
                    static::error(sprintf('Message channel (%s) is 30 seconds full, disconnected', $message->channel));
                    $this->interrupt();
                });
                $this->messageChannel->push($message);
                Timer::clear($timerID);
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
     * @throws \Throwable
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
     * @throws \Swoole\Exception
     */
    public function interrupt()
    {
        $this->connection->close();
        $this->resultChannel->close();
        $this->messageChannel->close();
        return true;
    }

    /**
     * Print error
     * @param \Throwable $ex
     */
    protected static function error(string $message)
    {
        $time = date('Y-m-d H:i:s');
        echo "[error] $time $message\n";
    }

}
