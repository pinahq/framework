<?php


namespace Pina\Commands;

use Pina\App;
use Pina\Command;
use Pina\DB\TriggerUpgrade;
use Pina\TableDataGateway;

class Update extends Command
{

    protected function execute($input = '')
    {
        App::modules()->walkRootClasses('Installation', function($cl) {
            $cl->prepare();
        });

        $upgrades = $this->getUpgrades();

        if ($input == 'test') {
            echo join("\n", $upgrades);
            return;
        }

        foreach ($upgrades as $upgrade) {
            echo $upgrade . "\n";
            App::db()->query($upgrade);
        }

        App::modules()->walkRootClasses('Installation', function($cl) {
            $cl->install();
        });
    }

    protected function getUpgrades()
    {
        $firstUpgrades = array();
        $lastUpgrades = array();
        $triggers = array();
        App::modules()->walkClasses(
            'Gateway',
            function (TableDataGateway $gw) use (&$firstUpgrades, &$lastUpgrades, &$triggers) {
                list($first, $last) = $gw->getUpgrades();
                $firstUpgrades = array_merge($firstUpgrades, $first);
                $lastUpgrades = array_merge($lastUpgrades, $last);
                $triggers = array_merge($triggers, $gw->getTriggers());
            }
        );

        $upgrades = array_merge($firstUpgrades, $lastUpgrades, TriggerUpgrade::getUpgrades($triggers));

        return $upgrades;
    }

}