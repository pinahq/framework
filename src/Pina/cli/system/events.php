<?php

namespace Pina;

$fname = Config::get('app', 'cronLockFile');

if (empty($fname)) {
    CLI::error('Please specify lock file path: cronLockFile in config/app.php');
    return;
}

$fp = fopen(Config::get('app', 'cronLockFile'), "w+");
if (flock($fp, LOCK_EX | LOCK_NB)) {
    $worker = new CronEventWorker();
    $worker->work();
} else {
    echo "Script was already running";
}

fclose($fp);
