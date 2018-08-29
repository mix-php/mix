<?php

echo \mix\helpers\XmlHelper::encode(compact('status', 'code', 'message', 'type', 'file', 'line', 'trace'));
