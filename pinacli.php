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

App::init('cli', $configDir);

$modules = App::modules();
$modules->load(Config::get('app', 'main') ? Config::get('app', 'main') : \Pina\Modules\App\Module::class);
$modules->boot('cli');

while ($cmd = array_shift($argv)) {
    if (!empty($cmd) && class_exists($cmd) && is_subclass_of($cmd, Command::class)) {
        $input = array_shift($argv);
        try {
            list($msec, $sec) = explode(' ', microtime());
            $startTime = (float)$msec + (float)$sec;

            /** @var Command $command */
            $command = App::load($cmd);
            $output = $command($input);

            list($msec, $sec) = explode(' ', microtime());
            $totalTime = (float)$msec + (float)$sec - $startTime;

            $context = [
                'cmd' => $cmd,
                'input' => $input,
                'output' => $output,
                'time' => $totalTime,
            ];
            Log::info('command', $command->__toString() . ": " . $output, $context);
            echo $output . "\n";
            echo $command->__toString() . ' ' . round($totalTime, 4) . 's done.' . "\n";
        } catch (Exception $e) {
            Log::error('system', $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), $e->getTrace());
            echo $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n";
            echo $command->__toString() . ' failed.' . "\n";
        }
        exit;
    }
}

echo 'Command not found...' . "\n";
