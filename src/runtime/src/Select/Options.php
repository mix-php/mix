<?php

namespace Mix\Select;

/**
 * Class Options
 * @package Mix\Select
 */
class Options
{

    /**
     * @var array [['clause' => ClauseIntercase, 'statement' => \Closure], ...]
     */
    public $cases;

    /**
     * @var \Closure
     */
    public $default;

}