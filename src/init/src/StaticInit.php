<?php

namespace Mix\Init;

/**
 * Class StaticInit
 * @package Mix\Init
 */
class StaticInit
{

    /**
     * @param string ...$paths
     * @return Finder
     */
    public static function finder(string ...$paths): Finder
    {
        return new Finder(...$paths);
    }

}
