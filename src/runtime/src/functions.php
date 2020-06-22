<?php

/**
 * 助手函数
 * @author liu,jian <coder.keda@gmail.com>
 */

if (!function_exists('xgo')) {
    /**
     * 创建协程
     * @param $function
     * @param mixed ...$params
     */
    function xgo($function, ...$params)
    {
        \Mix\Coroutine\Coroutine::create($function, ...$params);
    }
}

if (!function_exists('xdefer')) {
    /**
     * 创建延迟执行
     * @param $function
     */
    function xdefer($function)
    {
        return \Mix\Coroutine\Coroutine::defer($function);
    }
}

if (!function_exists('println')) {
    /**
     * 输出字符串并换行
     * @param mixed ...$values
     */
    function println(...$values)
    {
        $slice = [];
        foreach ($values as $value) {
            if (is_scalar($value)) {
                $slice[] = $value;
            } else {
                $slice[] = json_encode($value);
            }
        }
        echo implode(' ', $slice) . PHP_EOL;
    }
}

if (!function_exists('select')) {
    
    define ('SELECT_BREAK', 'BREAK');

    /**
     * @param Closure ...$clauses
     * @return \Mix\Select\Select
     */
    function select(\Closure ...$clauses): \Mix\Select\Select
    {
        return new \Mix\Select\Select(...$clauses);
    }

    /**
     * @param \Mix\Select\Clause\ClauseIntercase $clause
     * @param Closure $statement
     * @return Closure
     */
    function select_case(Mix\Select\Clause\ClauseIntercase $clause, \Closure $statement): \Closure
    {
        return \Mix\Select\Select::case($clause, $statement);
    }

    /**
     * @param Closure $statement
     * @return Closure
     */
    function select_default(\Closure $statement): \Closure
    {
        return \Mix\Select\Select::default($statement);
    }

    /**
     * @param \Mix\Coroutine\Channel $channel
     * @return \Mix\Select\Clause\ClauseIntercase
     */
    function select_pop(\Mix\Coroutine\Channel $channel): Mix\Select\Clause\ClauseIntercase
    {
        return \Mix\Select\Select::pop($channel);
    }

    /**
     * @param \Mix\Coroutine\Channel $channel
     * @param $value
     * @return \Mix\Select\Clause\ClauseIntercase
     */
    function select_push(\Mix\Coroutine\Channel $channel, $value): Mix\Select\Clause\ClauseIntercase
    {
        return \Mix\Select\Select::push($channel, $value);
    }

}


