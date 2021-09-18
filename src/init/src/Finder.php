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
     * @param string ...$paths
     * @return Finder
     */
    public static function in(string ...$paths): Finder
    {
        return new Finder(...$paths);
    }

    public function exec(string $methodname = 'init'): void
    {
        $finder = new \Symfony\Component\Finder\Finder;
        $iter = new \hanneskod\classtools\Iterator\ClassIterator($finder->in($this->paths));
        foreach ($iter->getClassMap() as $classname => $splFileInfo) {
            if (!class_exists($classname) || !method_exists($classname, $methodname)) {
                continue;
            }
            $ref = new \ReflectionMethod($classname, $methodname);
            if ($ref->isPublic() && $ref->isStatic()) {
                $classname::$methodname();
            }
        }
    }

}
