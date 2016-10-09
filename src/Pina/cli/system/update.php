<?php

namespace Pina;

$upgrades = [];
$triggers = [];
ModuleRegistry::walkClasses('Gateway', function($gw) use (&$upgrades, &$triggers) {
    $upgrades = array_merge($upgrades, $gw->getUpgrades());
    $triggers = array_merge($triggers, $gw->getTriggers());
});

$upgrades = array_merge($upgrades, TableDataGatewayTriggerUpgrade::getUpgrades($triggers));

$db = DB::get();
foreach ($upgrades as $q) {
    if (empty($q)) {
        continue;
    }
    $db->query($q);
}

if (!empty($upgrades) && is_array($upgrades)) {
    echo join($upgrades, "\r\n")."\r\n";
}