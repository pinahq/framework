<?php

use Pina\Access;
use Pina\Job;
use Pina\App;
use Pina\Route;
use Pina\Config;
use Pina\ModuleRegistry;

class JobTest extends PHPUnit_Framework_TestCase
{

    public function testPermit()
    {
        Config::init(__DIR__ . '/config');
        ModuleRegistry::init();

        Route::own('catalog', 'Pina');
        Job::register('catalog.import');

        $parts = explode('/', __DIR__);
        array_pop($parts);
        $appPath = implode('/', $parts);
        $jobPath = Job::getPath('catalog.import');
        if (strncmp($appPath, $jobPath, strlen($appPath)) === 0) {
            $jobPath = substr($jobPath, strlen($appPath));
        }

        $this->assertEquals('/src/Pina/job/catalog/import.php', $jobPath);
    }

}
