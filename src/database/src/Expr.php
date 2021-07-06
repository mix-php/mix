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
    protected $values;

    /**
     * Expr constructor.
     * @param string $expr
     * @param ...$values
     */
    public function __construct(string $expr, ...$values)
    {
        $this->expr = $expr;
        $this->values = $values;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $expr = $this->expr;
        foreach ($this->values as $value) {
            $expr = preg_replace('/\?/', is_string($value) ? "'%s'" : "%s", $expr, 1);
        }
        return vsprintf($expr, $this->values);
    }

}
