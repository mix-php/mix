<?php

// compact php >= 7.3 变量未定义会产生 E_NOTICE 级错误
$data = @compact('status', 'code', 'message', 'type', 'file', 'line');
if (isset($trace)) {
    $tmp = [];
    foreach (explode("\n", $trace) as $key => $item) {
        $tmp[strstr($item, ' ', true)] = trim(strstr($item, ' '));
    }
    $data['trace'] = $tmp;
}
echo \Mix\Helper\JsonHelper::encode($data);
