<?php

namespace Pina;

ModuleRegistry::walkClasses('Installation', function($cl) {
    $cl->install();
});