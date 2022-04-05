<?php

namespace App\Container\Swow\Http;

use App\Container\Logger;
use App\Container\Swow\Coroutine;
use Swow\CoroutineException;
use Swow\Errno;
use Swow\Http\ResponseException;
use Swow\Http\Server as HttpServer;
use Swow\Socket;
use Swow\SocketException;
use function Swow\Sync\waitAll;

class Server extends HttpServer
{
    /**
     * @var string|null
     */
    public $host = null;

    /**
     * @var int|null
     */
    public $port = null;

    /**
     * @var callable
     */
    protected $handler;

    /**
     * @param string $name
     * @param int $port
     * @param int $flags
     * @return static
     */
    public function bind(string $name, int $port = 0, int $flags = Socket::BIND_FLAG_NONE): static
    {
        $this->host = $name;
        $this->port = $port;
        parent::bind($name, $port, $flags);
        return $this;
    }

    public function handle(callable $callable)
    {
        $this->handler = $callable;
        return $this;
    }

    public function start()
    {
        $this->listen();
        Coroutine::create(function () {
            while (true) {
                try {
                    $connection = $this->acceptConnection();
                    Coroutine::create(function () use ($connection) {
                        try {
                            while (true) {
                                $request = null;
                                try {
                                    $request = $connection->recvHttpRequest();
                                    $handler = $this->handler;
                                    $handler($request, $connection);
                                } catch (ResponseException $exception) {
                                    $connection->error($exception->getCode(), $exception->getMessage());
                                }
                                if (!$request || !$request->getKeepAlive()) {
                                    break;
                                }
                            }
                        } catch (\Throwable $exception) {
                            Logger::instance()->error((string)$exception);
                        } finally {
                            $connection->close();
                        }
                    });
                } catch (SocketException|CoroutineException $exception) {
                    if (in_array($exception->getCode(), [Errno::EMFILE, Errno::ENFILE, Errno::ENOMEM], true)) {
                        Logger::instance()->warning('Socket resources have been exhausted.');
                        sleep(1);
                    } else {
                        Logger::instance()->error((string)$exception);
                        break;
                    }
                } catch (\Throwable $exception) {
                    Logger::instance()->error((string)$exception);
                }
            }
        });

        waitAll();
    }
}