#!/usr/bin/env php
<?php
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('memory_limit', '1G');

date_default_timezone_set('Asia/Shanghai');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require BASE_PATH . '/vendor/autoload.php';

use App\Container\Logger;
use App\Error;
use App\Vega;
use Dotenv\Dotenv;
use Swow\CoroutineException;
use Swow\Errno;
use Swow\Http\ResponseException;
use Swow\Http\Server as HttpServer;
use Swow\Socket;
use Swow\SocketException;
use function Swow\Sync\waitAll;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));
Error::register();

class SwowServer extends HttpServer
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
        \Swow\Coroutine::run(function () {
            while (true) {
                try {
                    $connection = $this->acceptConnection();
                    \Swow\Coroutine::run(function () use ($connection) {
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

$vega = Vega::new();
$server = new SwowServer();
$host = '0.0.0.0';
$port = 9501;
$server->bind($host, $port)->handle($vega->handler());
echo <<<EOL
                              ____
 ______ ___ _____ ___   _____  / /_ _____
  / __ `__ \/ /\ \/ /__ / __ \/ __ \/ __ \
 / / / / / / / /\ \/ _ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\  / .___/_/ /_/ .___/
                     /_/         /_/


EOL;
printf("System    Name:       %s\n", strtolower(PHP_OS));
printf("PHP       Version:    %s\n", PHP_VERSION);
printf("Swow      Version:    %s\n", '0.1.0');
printf("Listen    Addr:       http://%s:%d\n", $host, $port);
Logger::instance()->info('Start swow coroutine server');
$server->start();