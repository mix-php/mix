<?php

echo \mix\http\Xml::encode(compact('code', 'message', 'type', 'file', 'line', 'trace'));
