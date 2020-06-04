<?php

namespace Mix\Monolog\Handler;

use Mix\Console\CommandLine\Color;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Class ConsoleHandler
 * @package Mix\Monolog\Handler
 */
class ConsoleHandler extends AbstractProcessingHandler
{

    /**
     * ConsoleHandler constructor.
     * @param int $level
     * @param bool $bubble
     */
    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("[%datetime%] %channel%.%level_name%: [%file_line%] %message%\n", 'Y-m-d H:i:s', true);
    }

    /**
     * @param $level
     * @param string $message
     * @return string
     */
    protected function colour($level, string $message)
    {
        $start   = strpos($message, ': ');
        $label   = substr($message, 0, $start + 1);
        $content = substr($message, $start + 1);

        switch ($level) { // 渲染颜色
            case Logger::ERROR:
                $label = Color::new(Color::FG_RED)->sprint($label);
                break;
            case Logger::WARNING:
                $label = Color::new(Color::FG_YELLOW)->sprint($label);
                break;
            case Logger::NOTICE:
                $label = Color::new(Color::FG_GREEN)->sprint($label);
                break;
            case Logger::DEBUG:
                $label = Color::new(Color::FG_CYAN)->sprint($label);
                break;
            case Logger::INFO:
                $label = Color::new(Color::FG_BLUE)->sprint($label);
                break;
        }

        return sprintf('%s%s', $label, $content);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        $message = (string)$record['formatted'];
        $level   = $record['level'];

        // win系统普通打印
        if (!(stripos(PHP_OS, 'Darwin') !== false) && stripos(PHP_OS, 'WIN') !== false) {
            echo $message;
            return;
        }

        // 带颜色打印
        echo $this->colour($level, $message);
    }

}
