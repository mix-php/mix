<?php

namespace Mix\Server;

/**
 * Interface HandlerInterface
 * @package Mix\Server
 */
interface HandlerInterface
{

    /**
     * Handle
     * @param Connection $connection
     * @return mixed
     */
    public function handle(Connection $connection);

}
