<?php

namespace Mix\Micro\Gateway;

/**
 * Interface HandlerInterface
 * @package Mix\Micro\Gateway
 */
interface HandlerInterface extends \Mix\Http\Server\HandlerInterface
{

    /**
     * Server shutdown, clear resource
     */
    public function clear();

}
