<?php

namespace Pina;

$paths = ModuleRegistry::getPaths();
foreach ($paths as $ns => $path) {
    $files = array_filter(scandir($path), function($s) {
        return strrpos($s, 'Gateway') === (strlen($s) - 11);//11 is strlen of 'Gateway.php'
    });
    
    foreach ($files as $file) {
        $className = $ns.'\\'.pathinfo($file, PATHINFO_FILENAME);
        echo $className."\r\n";
        $gw = new $className;
        $upgrades = $gw->getUpgrades();
        $gw->doUpgrades();
    }
}