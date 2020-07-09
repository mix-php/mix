<?php

namespace Mix\Select\Clause;

use Mix\Select\Clause\ClauseIntercase;
use Mix\Coroutine\Channel;

/**
 * Class PopClause
 * @package Mix\Select\Clause
 */
class PopClause implements ClauseIntercase
{

    /**
     * @var Channel
     */
    protected $clauseChannel;

    /**
     * Push constructor.
     * @param Channel $channel
     * @param $value
     */
    public function __construct(Channel $channel)
    {
        $this->clauseChannel = $channel;
    }

    /**
     * Channel
     * @return Channel
     */
    public function channel(): Channel
    {
        return $this->clauseChannel;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        return $this->clauseChannel->pop();
    }

}