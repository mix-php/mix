<?php

use express\web\Json;
$json = new Json();
echo $json->encode(compact('code', 'message', 'type', 'file', 'line', 'trace'));
