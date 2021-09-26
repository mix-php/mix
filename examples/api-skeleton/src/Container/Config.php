<?php

namespace App\Container;

/**
 * Class Config
 * @package App\Container
 */
class Config
{

    /**
     * @var \Noodlehaus\Config
     */
    private static $instance;

    public static function init(): void
    {
        self::$instance = new \Noodlehaus\Config(__DIR__ . '/../../conf');
    }

    /**
     * @return \Noodlehaus\Config
     */
    public static function instance(): \Noodlehaus\Config
    {
        if (!isset(self::$instance)) {
            static::init();
        }
        return self::$instance;
    }

}
