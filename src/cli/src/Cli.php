<?php

namespace Mix\Cli;

/**
 * Class Cli
 * @package Mix\Cli
 */
class Cli
{

    /**
     * @var Application
     */
    protected static $app;

    /**
     * Init
     */
    public static function init(): void
    {
        static::$app = new Application('app', '0.0.0');
    }

    /**
     * @return Application
     */
    public static function app(): Application
    {
        return static::$app;
    }

    /**
     * @param string $name
     * @return Application
     */
    public static function setName(string $name): Application
    {
        static::$app->name = $name;
        return static::$app;
    }

    /**
     * @param string $version
     * @return Application
     */
    public static function setVersion(string $version): Application
    {
        static::$app->version = $version;
        return static::$app;
    }

    /**
     * @param bool $debug
     * @return Application
     */
    public static function setDebug(bool $debug): Application
    {
        static::$app->debug = $debug;
        return static::$app;
    }

    /**
     * @param \Closure ...$handlerFunc
     * @return Application
     */
    public static function use(\Closure ...$handlerFunc): Application
    {
        return static::$app->use(...$handlerFunc);
    }

    /**
     * @param Command ...$commands
     * @return Application
     */
    public static function addCommand(Command ...$commands): Application
    {
        return static::$app->addCommand(...$commands);
    }

    /**
     * Run
     */
    public static function run(): void
    {
        static::$app->run();
    }

}
