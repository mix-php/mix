<?php

echo \mix\helpers\JsonHelper::encode(compact('status', 'code', 'message', 'type', 'file', 'line', 'trace'));
