<?php

namespace App\Command;

use Mix\Cli\RunInterface;
use App\Container\DB;
use App\Container\RDS;

class CoroutineRun implements RunInterface
{

    public function main(): void
    {
        $func = function () {
            // do something
        };
        \Swoole\Coroutine\run(function () use ($func) {
            DB::enableCoroutine();
            RDS::enableCoroutine();
            $func();
        });
    }

}
