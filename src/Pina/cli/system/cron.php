<?php

namespace Pina;

$scheduler = new Scheduler();
$modules = App::modules();
foreach ($modules as $module) {
    if (method_exists($module, 'schedule')) {
        $module->schedule($scheduler);
    }
}
$scheduler->run();