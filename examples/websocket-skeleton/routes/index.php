<?php

use App\Controller\WebSocket;

return function (Mix\Vega\Engine $vega) {
    $vega->handle('/websocket', [new WebSocket(), 'index'])->methods('GET');
};
