<?php

use express\web\Xml;
$xml  = new Xml();
$data = [];
foreach (['code', 'message', 'file', 'line', 'trace'] as $name) {
    isset($$name) and $data[$name] = $$name;
}
echo $xml->encode($data);
