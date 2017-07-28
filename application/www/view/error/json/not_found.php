<?php

use mix\web\Json;
$json = new Json();
echo $json->encode(compact('code', 'message'));
