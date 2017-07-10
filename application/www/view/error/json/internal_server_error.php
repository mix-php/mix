<?= json_encode(['code' => $code, 'message' => $message, 'file' => $file, 'line' => $line, 'trace' => explode("\n", $trace)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
