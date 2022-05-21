<?php

namespace App\Container;

use App\Once;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;

/**
 * Class Logger
 * @package App\Container
 */
class Logger implements HandlerInterface
{

    /**
     * @var \Monolog\Logger
     */
    private static $instance;

    /**
     * @var Once
     */
    private static $once;

    /**
     * @return void
     */
    public static function init(): void
    {
        self::$once = new Once();
    }

    /**
     * @return \Monolog\Logger
     */
    public static function instance(): \Monolog\Logger
    {
        if (!isset(self::$instance)) {
            static::$once->do(function () {
                $logger = new \Monolog\Logger('MIX');
                $rotatingFileHandler = new RotatingFileHandler(__DIR__ . '/../../runtime/logs/mix.log', 7);
                $rotatingFileHandler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n", 'Y-m-d H:i:s.u'));
                $logger->pushHandler($rotatingFileHandler);
                $logger->pushHandler(new Logger());
                self::$instance = $logger;
            });
        }
        return self::$instance;
    }

    /**
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record): bool
    {
        if (APP_DEBUG) {
            return $record['level'] >= \Monolog\Logger::DEBUG;
        }
        return $record['level'] >= \Monolog\Logger::INFO;
    }

    /**
     * @param array $record
     * @return bool
     */
    public function handle(array $record): bool
    {
        $message = sprintf("%s  %s  %s\n", $record['datetime']->format('Y-m-d H:i:s.u'), $record['level_name'], $record['message']);
        switch (PHP_SAPI) {
            case 'cli':
            case 'cli-server':
                file_put_contents("php://stdout", $message);
                break;
        }
        return false;
    }

    /**
     * @param array $records
     * @return void
     */
    public function handleBatch(array $records): void
    {
        // TODO: Implement handleBatch() method.
    }

    /**
     * @return void
     */
    public function close(): void
    {
        // TODO: Implement close() method.
    }

}

Logger::init();
