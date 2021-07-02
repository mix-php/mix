<?php

namespace Mix\Database;

/**
 * Class Expr
 * @package Mix\Database
 */
class Expr
{

    /**
     * @var string
     */
    protected $expr;

    /**
     * @var array
     */
    protected $args;

    /**
     * Expr constructor.
     * @param string $expr
     * @param ...$args
     */
    public function __construct(string $expr, ...$args)
    {
        $this->expr = $expr;
        $this->args = $args;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return vsprintf(str_replace('?', "'%s'", $this->expr), $this->args);
    }

}
