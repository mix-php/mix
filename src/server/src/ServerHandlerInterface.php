<?php

namespace Mix\Server;

/**
 * Interface ServerHandlerInterface
 * @package Mix\Server
 */
interface ServerHandlerInterface
{

    /**
     * Handle
     * @param Connection $connection
     */
    public function handle(Connection $connection);

}
