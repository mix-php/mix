<?php

namespace Mix\SyncInvoke;

use Mix\Server\Connection;
use Mix\Server\Exception\ReceiveException;
use Mix\Server\HandlerInterface;
use Mix\SyncInvoke\Exception\CallException;
use Psr\Log\LoggerInterface;

/**
 * Class Handler
 * @package Mix\SyncInvoke
 */
class Handler implements HandlerInterface
{

    /**
     * @var LoggerInterface
     */
    public $log;

    /**
     * Handle
     * @param Connection $connection
     */
    public function handle(Connection $connection)
    {
        while (true) {
            try {
                $data      = $connection->recv();
                $overview  = preg_replace('/\s/', '', substr($data, 40, 300));
                $closure   = \Opis\Closure\unserialize($data);
                $microtime = static::microtime();
                try {
                    $result = call_user_func($closure);
                } catch (\Throwable $e) {
                    $message = sprintf('%s in %s on line %s', $e->getMessage(), $e->getFile(), $e->getLine());
                    $code    = $e->getCode();
                    $connection->send(serialize(new CallException($message, $code)) . Server::EOF);
                    $this->log('warning', '{code} | {message} | {overview}', [
                        'code'     => $code,
                        'message'  => $message,
                        'overview' => $overview,
                    ]);
                    continue;
                }
                $time = round((static::microtime() - $microtime) * 1000, 2);
                $this->log('info', '{time}ms | {overview}', [
                    'time'     => $time,
                    'overview' => $overview,
                ]);
                $connection->send(serialize($result) . Server::EOF);
            } catch (\Throwable $e) {
                // 忽略服务器主动断开连接异常
                if ($e instanceof ReceiveException && in_array($e->getCode(), [54, 104])) { // mac=54, linux=104
                    return;
                }
                // 抛出异常
                throw $e;
            }
        }
    }

    /**
     * 获取微秒时间
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Print log
     * @param string $level
     * @param string $message
     * @param array $context
     */
    protected function log(string $level, string $message, array $context = [])
    {
        if (!isset($this->log)) {
            return;
        }
        $this->log->log($level, $message, $context);
    }

}
