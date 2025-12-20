#!/usr/bin/env php
<?php

use Pina\App;
use Pina\Command;
use Pina\Config;
use Pina\Log;

$configDir = __DIR__ . '/config';
if (file_exists(__DIR__ . '/../../autoload.php')) {
    require __DIR__ . '/../../autoload.php';
    $configDir = __DIR__ . '/../../../config';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    throw new RuntimeException('Unable to locate autoload.php file.');
}

App::init($configDir);

echo 'Hello from Pina framework shell'."\n";
App::cli()->run($argv);
