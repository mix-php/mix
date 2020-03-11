<?php

namespace Mix\SyncInvoke;

use Mix\Server\Connection;
use Mix\Server\Exception\ReceiveException;
use Mix\Server\HandlerInterface;
use Mix\SyncInvoke\Event\InvokedEvent;
use Mix\SyncInvoke\Exception\CallException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Server
 * @package Mix\SyncInvoke
 */
class Server implements HandlerInterface
{

    /**
     * @var int
     */
    public $port = 0;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * @var \Mix\Server\Server
     */
    protected $server;

    /**
     * Server constructor.
     * @param int $port
     * @param bool $reusePort
     */
    public function __construct(int $port, bool $reusePort = false)
    {
        $this->port      = $port;
        $this->reusePort = $reusePort;
    }

    /**
     * Start
     * @throws \Swoole\Exception
     */
    public function start()
    {
        $server = $this->server = new \Mix\Server\Server('127.0.0.1', $this->port, false, $this->reusePort);
        $server->set([
            'open_eof_check' => true,
            'package_eof'    => Constants::EOF,
        ]);
        $server->start($this);
    }

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
                    $message = sprintf('%s %s in %s on line %s', $e->getMessage(), get_class($e), 'closure://function () {...}', $e->getLine());
                    $code    = $e->getCode();

                    $event        = new InvokedEvent();
                    $event->time  = round((static::microtime() - $microtime) * 1000, 2);
                    $event->raw   = $data;
                    $event->error = sprintf('[%d] %s', $code, $message);
                    $this->dispatch($event);

                    $connection->send(serialize(new CallException($message, $code)) . Constants::EOF);
                    continue;
                }

                $event       = new InvokedEvent();
                $event->time = round((static::microtime() - $microtime) * 1000, 2);
                $event->raw  = $data;
                $this->dispatch($event);

                $connection->send(serialize($result) . Constants::EOF);
            } catch (\Throwable $e) {
                // 忽略服务器主动断开连接异常
                if ($e instanceof ReceiveException && in_array($e->getCode(), [54, 104])) { // mac=54, linux=104
                    return;
                }
                // 断开连接
                $connection->close();
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
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        $this->server->shutdown();
    }

}
