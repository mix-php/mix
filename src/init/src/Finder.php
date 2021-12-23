<?php

namespace Mix\Init;

use Mix\Init\Composer\ClassMapGenerator;

/**
 * Class Finder
 * @package Mix\Init
 */
class Finder
{

    /**
     * @var string[]
     */
    protected $dirs = [];

    /**
     * Finder constructor.
     * @param string ...$dirs
     */
    public function __construct(string ...$dirs)
    {
        $this->dirs = $dirs;
    }

    /**
     * @param string ...$methods
     * @throws \ReflectionException
     */
    public function exec(string ...$methods): void
    {
        $maps = array();
        foreach ($this->dirs as $dir) {
            $maps = array_merge($maps, ClassMapGenerator::createMap($dir));
        }

        foreach ($maps as $class => $path) {
            foreach ($methods as $method) {
                if (!class_exists($class) || !method_exists($class, $method)) {
                    continue;
                }
                $ref = new \ReflectionMethod($class, $method);
                if ($ref->isPublic() && $ref->isStatic()) {
                    $class::$method();
                }
            }
        }
    }

}
