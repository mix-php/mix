<?php

// Autoload
require __DIR__ . '/../vendor/autoload.php';

// Environment
$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/../.env');

// Run application
$config = require __DIR__ . '/../app/manifest.php';
(new Mix\Console\Application($config))->run();
