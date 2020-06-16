<?php

namespace Mix\Select;

/**
 * Class Clauses
 * @package Mix\Select
 */
class Clauses
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