<?php

namespace Pina;

$scheduler = new Scheduler();
$modules = App::modules();
foreach ($modules as $module) {
    $module->schedule($scheduler);
}
$scheduler->run();