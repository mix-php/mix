<?php

namespace Mix\Select\Clause;

use Mix\Select\Clause\ClauseIntercase;
use Mix\Coroutine\Channel;

/**
 * Class PushClause
 * @package Mix\Select\Clause
 */
class PushClause implements ClauseIntercase
{

    /**
     * @var Channel
     */
    protected $clauseChannel;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Push constructor.
     * @param Channel $channel
     * @param $value
     */
    public function __construct(Channel $channel, $value)
    {
        $this->clauseChannel = $channel;
        $this->value         = $value;
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
        return $this->clauseChannel->push($this->value);
    }

}