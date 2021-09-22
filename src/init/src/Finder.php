<?php

namespace Mix\Init;

/**
 * Class Finder
 * @package Mix\Init
 */
class Finder
{

    /**
     * @var string[]
     */
    protected $paths = [];

    /**
     * Finder constructor.
     * @param string ...$paths
     */
    public function __construct(string ...$paths)
    {
        $this->paths = $paths;
    }

    /**
     * @param string ...$methods
     * @throws \ReflectionException
     */
    public function exec(string ...$methods): void
    {
        $finder = new \Symfony\Component\Finder\Finder;
        $iter = new \hanneskod\classtools\Iterator\ClassIterator($finder->in($this->paths));
        foreach ($iter->getClassMap() as $class => $splFileInfo) {
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
