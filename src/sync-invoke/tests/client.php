<?php

include '../vendor/autoload.php';
include 'class.php';

Swoole\Coroutine\run(function () {

    $dialer = new \Mix\SyncInvoke\Client\Dialer();
    $conn   = $dialer->dial(9505);
    $data   = $conn->invoke(function () {
        $obj = new Hello();
        return [1, 2, 3, $obj];
    });
    var_dump($data);

});
