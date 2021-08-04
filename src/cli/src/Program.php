<?php

namespace Mix\Cli;

/**
 * Class Program
 * @package Mix\Cli
 */
class Program
{

    /**
     * ./foo.php
     *
     * @var string
     */
    public $path = '';

    /**
     * /data/project/bin/foo.php
     *
     * @var string
     */
    public $absPath = '';

    /**
     * /data/project/bin
     *
     * @var string
     */
    public $dir = '';

    /**
     * foo.php
     *
     * @var string
     */
    public $file = '';

}
