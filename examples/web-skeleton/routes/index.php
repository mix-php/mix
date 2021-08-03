<?php

use App\Controller\Hello;

return function (Mix\Vega\Engine $vega) {
    $vega->handle('/', [new Hello(), 'index'])->methods('GET');
};
