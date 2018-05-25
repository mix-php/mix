<?php

echo \mix\http\Json::encode(compact('code', 'message', 'type', 'file', 'line', 'trace'));
