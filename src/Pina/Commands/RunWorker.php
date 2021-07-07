<?php


namespace Pina\Commands;


use Pina\Command;
use Pina\Config;
use Pina\CronEventWorker;

class RunWorker extends Command
{

    protected function execute($input = '')
    {
        $fname = Config::get('app', 'cronLockFile');
        if (empty($fname)) {
            echo 'Please specify lock file path: cronLockFile in config/app.php' . "\n";
            return;
        }

        $fp = fopen(Config::get('app', 'cronLockFile'), "w+");
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            $worker = new CronEventWorker();
            $worker->work();
        } else {
            echo 'Script was already running' . "\n";
        }

        fclose($fp);
    }

}