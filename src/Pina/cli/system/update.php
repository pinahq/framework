<?php

namespace Pina;

$upgrades = App::getUpgrades();

$db = App::container()->get(\Pina\DatabaseDriverInterface::class);
$db->batch($upgrades);

if (!empty($upgrades) && is_array($upgrades)) {
    echo join($upgrades, "\r\n")."\r\n";
}