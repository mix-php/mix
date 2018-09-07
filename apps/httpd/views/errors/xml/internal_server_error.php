<?php

$data = compact('status', 'code', 'message', 'type', 'file', 'line');
if (isset($trace)) {
    $data += ['trace' => explode("\n", $trace)];
}
echo \mix\helpers\XmlHelper::encode($data);
