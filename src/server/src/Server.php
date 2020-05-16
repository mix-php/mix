<?php

namespace Mix\Server;

/**
 * Class Server
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class Server
{

    /**
     * @var string
     */
    public $host = '';

    /**
     * @var int
     */
    public $port = 0;

    /**
     * @var bool
     */
    public $ssl = false;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * @var ConnectionManager
     */
    public $connectionManager;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var \Swoole\Coroutine\Server
     */
    public $swooleServer;

    /**
     * Server constructor.
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @param bool $reusePort
     */
    public function __construct(string $host, int $port, bool $ssl = false, bool $reusePort = false)
    {
        $this->host              = $host;
        $this->port              = $port;
        $this->ssl               = $ssl;
        $this->reusePort         = $reusePort;
        $this->connectionManager = new ConnectionManager();
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
     * Handle
     * @param callable $callback
     */
    public function handle(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Start
     * @param ServerHandlerInterface|null $handler
     * @throws \Swoole\Exception
     */
    public function start(ServerHandlerInterface $handler = null)
    {
        if (!is_null($handler)) {
            $this->handle([$handler, 'handle']);
        }
        $server     = $this->swooleServer = new \Swoole\Coroutine\Server($this->host, $this->port, $this->ssl, $this->reusePort);
        $this->port = $server->port; // 当随机分配端口时同步端口信息
        $server->set($this->options);
        $server->handle(function (\Swoole\Coroutine\Server\Connection $connection) {
            try {
                // 生成连接
                $connection = new Connection($connection, $this->connectionManager);
                $this->connectionManager->add($connection);
                // 执行回调
                call_user_func($this->callback, $connection);
            } catch (\Throwable $e) {
                // 错误处理
                $isMix = class_exists(\Mix::class);
                if (!$isMix) {
                    throw $e;
                }
                /** @var \Mix\Console\Error $error */
                $error = \Mix::$app->context->get('error');
                $error->handleException($e);
            }
        });
        if (!$server->start()) {
            throw new \Swoole\Exception($server->errMsg ?? 'none', $server->errCode);
        }
    }

    /**
     * Shutdown
     * @throws \Swoole\Exception
     */
    public function shutdown()
    {
        if (!$this->swooleServer) {
            return;
        }
        if (!$this->swooleServer->shutdown()) {
            if ($this->swooleServer->errCode == 0) {
                return;
            }
            throw new \Swoole\Exception($this->swooleServer->errMsg ?? 'none', $this->swooleServer->errCode);
        }
        $this->connectionManager->closeAll();
    }

}
