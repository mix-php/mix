<?php

namespace Mix\WorkerPool;

/**
 * Interface RunInterface
 * @package Mix\WorkerPool
 */
interface RunInterface
{

    public function do($data): void;

}
