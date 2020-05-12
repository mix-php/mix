<?php

if (!function_exists('run')) {
    function run($func)
    {
        $scheduler = new \Swoole\Coroutine\Scheduler;
        $scheduler->set([
            'hook_flags' => SWOOLE_HOOK_ALL,
        ]);
        $scheduler->add(function () use ($func) {
            call_user_func($func);
        });
        $scheduler->start();
    }
}
