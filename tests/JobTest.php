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
//        /App::env('test');
        Config::initPath(__DIR__.'/config');
        ModuleRegistry::init();
        
        Route::own('catalog', 'Pina');
        Job::register('catalog.import');
        
        $this->assertEquals('/home/cody/www/framework/src/Pina/job/catalog/import.php', Job::getPath('catalog.import'));
    }

}
