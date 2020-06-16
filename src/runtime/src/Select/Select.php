<?php

namespace Mix\Select;

use Mix\Coroutine\Coroutine;
use Mix\Select\Clause\ClauseIntercase;
use Mix\Select\Clause\Pop;
use Mix\Select\Clause\Push;
use Mix\Coroutine\Channel;

/**
 * Class Select
 * @package Mix\Select
 */
class Select
{

    const RETURN = true;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var bool
     */
    protected $return = false;

    /**
     * @var \Swoole\Coroutine\Channel
     */
    protected $waitChannel;

    /**
     * Select constructor.
     * @param \Closure ...$options
     */
    public function __construct(\Closure ...$options)
    {
        $this->options = new Options();
        foreach ($options as $option) {
            call_user_func($option, $this->options);
        }
    }

    /**
     * Case
     * @param ClauseIntercase $clause
     * @param \Closure $statement
     * @return \Closure
     */
    public static function case(ClauseIntercase $clause, \Closure $statement): \Closure
    {
        return function (Options $options) use ($clause, $statement) {
            $options->cases[] = [
                'clause'    => $clause,
                'statement' => $statement,
            ];
        };
    }

    /**
     * Default
     * @param \Closure $statement
     * @return \Closure
     */
    public static function default(\Closure $statement): \Closure
    {
        return function (Options $options) use ($statement) {
            $options->default = $statement;
        };
    }

    /**
     * Pop
     * @return ClauseIntercase
     */
    public static function pop(Channel $channel): ClauseIntercase
    {
        return new Pop($channel);
    }

    /**
     * Push
     * @return ClauseIntercase
     */
    public static function push(Channel $channel, $value): ClauseIntercase
    {
        return new Push($channel, $value);
    }

    /**
     * Run
     * @return $this
     */
    public function run()
    {
        $options = $this->options;

        $processes = [];
        foreach ($options->cases as $case) {
            /** @var ClauseIntercase $clause */
            $clause    = $case['clause'];
            $statement = $case['statement'];
            if ($clause instanceof Push && !$clause->channel()->isFull()) {
                $processes[] = function () use ($clause, $statement) {
                    $clause->run();
                    $return       = call_user_func($statement);
                    $this->return = $return ? true : false;
                };
            }
            if ($clause instanceof Pop && !$clause->channel()->isEmpty()) {
                $processes[] = function () use ($clause, $statement) {
                    $value        = $clause->run();
                    $return       = call_user_func($statement, $value);
                    $this->return = $return ? true : false;
                };
            }
        }

        if (!empty($processes)) {
            call_user_func($processes[array_rand($processes)]);
            return $this;
        }

        if ($options->default) {
            call_user_func($options->default);
            return $this;
        }

        // 阻塞，直到某个通信可以运行
        $this->waitAndRun();
        return $this;
    }

    /**
     * Wait and run
     */
    protected function waitAndRun()
    {
        $this->waitChannel = $waitChannel = new \Swoole\Coroutine\Channel(); // 必须是 Swoole 的 Channel
        $options           = $this->options;
        foreach ($options->cases as $case) {
            /** @var ClauseIntercase $clause */
            $clause = $case['clause'];
            $clause->channel()->addNotifier($waitChannel);
        }
        $processe = null;
        while (true) {
            $waitChannel->pop();
            foreach ($options->cases as $case) {
                /** @var ClauseIntercase $clause */
                $clause    = $case['clause'];
                $statement = $case['statement'];
                if ($clause instanceof Push && !$clause->channel()->isFull()) {
                    $processe = function () use ($clause, $statement) {
                        $clause->run();
                        $return       = call_user_func($statement);
                        $this->return = $return ? true : false;
                    };
                    break;
                }
                if ($clause instanceof Pop && !$clause->channel()->isEmpty()) {
                    $processe = function () use ($clause, $statement) {
                        $value        = $clause->run();
                        $return       = call_user_func($statement, $value);
                        $this->return = $return ? true : false;
                    };
                    break;
                }
            }
            if ($processe) {
                break;
            }
        }
        call_user_func($processe);
    }

    /**
     * @return bool
     */
    public function return()
    {
        return $this->return;
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        $waitChannel = $this->waitChannel;
        if ($waitChannel) {
            $options = $this->options;
            foreach ($options->cases as $case) {
                /** @var ClauseIntercase $clause */
                $clause = $case['clause'];
                $clause->channel()->delNotifier($waitChannel);
            }
        }
    }

}
