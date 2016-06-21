<?php

namespace Pina;

ModuleRegistry::walkClasses('Gateway', function($gw) {
    $upgrades = $gw->doUpgrades();
    if (!empty($upgrades) && is_array($upgrades)) {
        echo join($upgrades, "\r\n")."\r\n";
    }
});