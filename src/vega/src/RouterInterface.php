<?php

namespace Mix\Vega;

/**
 * Interface RouterInterface
 * @package Mix\Vega
 */
interface RouterInterface
{

    public function get(string $path, \Closure ...$handlers): RouterInterface;

}
