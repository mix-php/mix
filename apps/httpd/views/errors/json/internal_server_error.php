<?php

$data = compact('status', 'code', 'message', 'type', 'file', 'line');
if (isset($trace)) {
    $tmp = [];
    foreach (explode("\n", $trace) as $key => $item) {
        $tmp[strstr($item, ' ', true)] = trim(strstr($item, ' '));
    }
    $data['trace'] = $tmp;
}
echo \mix\helpers\JsonHelper::encode($data);
