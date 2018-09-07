<?php

$data = compact('status', 'code', 'message', 'type', 'file', 'line');
if (isset($trace)) {
    $tmp = [];
    foreach (explode("\n", $trace) as $key => $item) {
        $tmp['item' . substr(strstr($item, ' ', true), 1)] = trim(strstr($item, ' '));
    }
    $data['trace'] = $tmp;
}
echo \mix\helpers\XmlHelper::encode($data);
