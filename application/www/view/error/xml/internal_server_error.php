<?php

use express\web\Xml;
$xml = new Xml();
echo $xml->encode(['code' => $code, 'message' => $message, 'file' => $file, 'line' => $line, 'trace' => $trace]);
