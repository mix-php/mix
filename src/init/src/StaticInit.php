<?php

namespace Mix\Init;

/**
 * Class StaticInit
 * @package Mix\Init
 */
class StaticInit
{

    /**
     * @param string ...$dirs
     * @return Finder
     */
    public static function finder(string ...$dirs): Finder
    {
        return new Finder(...$dirs);
    }

}
