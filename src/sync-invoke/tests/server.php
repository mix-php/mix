<?php

include '../vendor/autoload.php';
include 'class.php';

Swoole\Coroutine\run(function () {

    $server = new \Mix\SyncInvoke\Server(9505, true);
    $server->start();

});
