<?php

namespace Mix\Redis;

/**
 * Class Multi
 * @package Mix\Redis
 */
class Multi extends Connection
{

    /**
     * @var bool
     */
    protected $watch = false;

    /**
     * @var bool
     */
    protected $inTransaction = true;

    /**
     * Multi constructor.
     * @param Driver $driver
     * @param LoggerInterface|null $logger
     * @param bool $watch
     */
    public function __construct(Driver $driver, ?LoggerInterface $logger, bool $watch = false)
    {
        parent::__construct($driver, $logger);
        $this->watch = $watch;

        $this->__call('multi', [\Redis::MULTI]);
    }

    /**
     * @return bool
     */
    protected function inTransaction(): bool
    {
        if (!$this->inTransaction) {
            return false;
        }
        if ($this instanceof Multi) {
            return true;
        }
        return false;
    }

    public function discard()
    {
        $this->__call('discard');
        if ($this->watch) {
            $this->__call('unwatch');
        }
        $this->inTransaction = false;
        $this->__destruct();
    }

    /**
     * @return array
     */
    public function exec()
    {
        $result = $this->__call('exec');
        $this->inTransaction = false;
        $this->__destruct();
        return $result;
    }

}
