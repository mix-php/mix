<?php

$data = [];
foreach (['code', 'message', 'file', 'line', 'trace'] as $name) {
    isset($$name) and $data[$name] = $$name;
}
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
