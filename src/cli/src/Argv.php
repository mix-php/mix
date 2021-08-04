<?php

namespace Mix\Cli;

/**
 * Class ArgumentVector
 * @package Mix\Cli
 */
class Argv
{

    /**
     * @var Program
     */
    protected static $program;

    /**
     * @var string
     */
    protected static $command = '';

    /**
     * @param bool $singleton
     */
    public static function parse(bool $singleton = false): void
    {
        $argv = $GLOBALS['argv'];
        $program = new Program();
        $program->path = $argv[0];
        $program->absPath = realpath($argv[0]);
        $program->dir = dirname($program->absPath);
        $program->file = basename($program->absPath);
        static::$program = $program;

        if (count($GLOBALS['argv']) <= 1 || $singleton) {
            static::$command = '';
            return;
        }
        $command = $argv[1] ?? '';
        $command = preg_match('/^[a-zA-Z0-9_\-:]+$/i', $command) ? $command : '';
        $command = substr($command, 0, 1) == '-' ? '' : $command;
        static::$command = trim($command);
    }

    /**
     * @return Program
     */
    public static function program(): Program
    {
        return static::$program;
    }

    /**
     * @return string
     */
    public static function command(): string
    {
        return static::$command;
    }

}
