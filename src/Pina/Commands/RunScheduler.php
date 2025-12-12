<?php


namespace Pina\Commands;


use Pina\App;
use Pina\Command;
use Pina\Scheduler;

class RunScheduler extends Command
{
    protected function execute($input = '')
    {
        $scheduler = App::load(Scheduler::class);
        $modules = App::modules();
        foreach ($modules as $module) {
            if (method_exists($module, 'schedule')) {
                $module->schedule($scheduler);
            }
        }
        $scheduler->run();
    }
}