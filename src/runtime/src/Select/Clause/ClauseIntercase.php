<?php

namespace Mix\Select\Clause;

use Mix\Coroutine\Channel;

/**
 * Interface ClauseIntercase
 * @package Mix\Select\Clause
 */
interface ClauseIntercase
{

    /**
     * Channel
     * @return Channel
     */
    public function channel(): Channel;

    /**
     * @return mixed
     */
    public function run();

}
