<?php

namespace Mix\SyncInvoke;

use Mix\Server\Connection;
use Mix\Server\Exception\ReceiveException;
use Mix\SyncInvoke\Event\CalledEvent;
use Mix\SyncInvoke\Exception\CallException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Server
 * @package Mix\SyncInvoke
 */
class Server implements \Mix\Server\ServerHandlerInterface
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
     * @var array
     */
    protected $options = [];

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
     * Set
     * @param array $options
     */
    public function set(array $options)
    {
        $this->options = $options;
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
            ] + $this->options);
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
                $code      = $connection->recv();
                $closure   = \Opis\Closure\unserialize($code);
                $microtime = static::microtime();
                $error     = null;
                try {
                    $result = call_user_func($closure);
                } catch (\Throwable $ex) {
                    $message = sprintf('%s %s in %s on line %s', $ex->getMessage(), get_class($ex), 'closure://function () {...}', $ex->getLine());
                    $error   = sprintf('[%d] %s', $ex->getCode(), $message);
                    $connection->send(serialize(new CallException($message, $ex->getCode())) . Constants::EOF);
                    continue;
                } finally {
                    $this->dispatch($code, $microtime, $error);
                }
                $connection->send(serialize($result) . Constants::EOF);
            } catch (\Throwable $ex) {
                // 忽略服务器主动断开连接异常
                if ($ex instanceof ReceiveException && in_array($ex->getCode(), [54, 104])) { // mac=54, linux=104
                    return;
                }
                // 断开连接
                $connection->close();
                // 抛出异常
                throw $ex;
            }
        }
    }

    /**
     * 获取当前时间, 单位: 秒, 粒度: 微秒
     * @return float
     */
    protected static function microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Dispatch
     * @param string $code
     * @param float $microtime
     * @param string|null $error
     */
    protected function dispatch(string $code, float $microtime, string $error = null)
    {
        if (!isset($this->dispatcher)) {
            return;
        }
        $event        = new CalledEvent();
        $event->time  = round((static::microtime() - $microtime) * 1000, 2);
        $event->code  = $code;
        $event->error = $error;
        $this->dispatcher->dispatch($event);
    }
    
    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        $this->server and $this->server->shutdown();
    }

}
