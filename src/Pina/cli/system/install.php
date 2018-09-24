<?php

namespace Pina;

App::walkClasses('Installation', function($cl) {
    $cl->install();
});