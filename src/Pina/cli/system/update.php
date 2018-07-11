<?php

namespace Pina;

$upgrades = App::getUpgrades();

$db = DB::get();
$db->batch($upgrades);

if (!empty($upgrades) && is_array($upgrades)) {
    echo join($upgrades, "\r\n")."\r\n";
}