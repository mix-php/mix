<?php

namespace App\Command;

use Mix\Cli\RunInterface;
use Mix\Init\StaticInit;
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
            StaticInit::finder(__DIR__ . '/../../src/Container')->exec('init');
            DB::enableCoroutine();
            RDS::enableCoroutine();
            $func();
        });
    }

}
