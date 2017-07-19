<?php

use express\web\Xml;
$xml = new Xml();
echo $xml->encode(compact('code', 'message', 'type', 'file', 'line', 'trace'));
