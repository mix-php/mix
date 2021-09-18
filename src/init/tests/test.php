<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Example.php';

Mix\Init\StaticInit::finder(__DIR__)->exec('init');
