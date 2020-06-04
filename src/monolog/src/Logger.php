<?php

namespace Mix\Monolog;

use Monolog\DateTimeImmutable;

/**
 * Class Logger
 * @package Mix\Monolog
 */
class Logger extends \Monolog\Logger
{

    /**
     * Set name
     */
    public function withName(string $name): \Monolog\Logger
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Adds a log record.
     *
     * @param int $level The logging level
     * @param string $message The log message
     * @param array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function addRecord(int $level, string $message, array $context = []): bool
    {
        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        foreach ($this->handlers as $key => $handler) {
            if ($handler->isHandling(['level' => $level])) {
                $handlerKey = $key;
                break;
            }
        }

        if (null === $handlerKey) {
            return false;
        }

        $levelName = static::getLevelName($level);

        $debugTraces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $debugTrace  = array_pop($debugTraces);
        $fileLine    = sprintf('%s:%s', basename($debugTrace['file']), $debugTrace['line']);

        $record = [
            'file_line'  => $fileLine,
            'message'    => $message,
            'context'    => $context,
            'level'      => $level,
            'level_name' => $levelName,
            'channel'    => $this->name,
            'datetime'   => new DateTimeImmutable($this->microsecondTimestamps, $this->timezone),
            'extra'      => [],
        ];

        try {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }

            // advance the array pointer to the first handler that will handle this record
            reset($this->handlers);
            while ($handlerKey !== key($this->handlers)) {
                next($this->handlers);
            }

            while ($handler = current($this->handlers)) {
                if (true === $handler->handle($record)) {
                    break;
                }

                next($this->handlers);
            }
        } catch (Throwable $e) {
            $this->handleException($e, $record);
        }

        return true;
    }

}
