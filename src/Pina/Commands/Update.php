<?php


namespace Pina\Commands;

use Pina\App;
use Pina\Command;

class Update extends Command
{

    protected function execute($input = '')
    {
        App::walkModuleRootClasses('Installation', function($cl) {
            $cl->prepare();
        });

        $upgrades = App::getUpgrades();

        if ($input == 'test') {
            echo join("\n", $upgrades);
            return;
        }

        foreach ($upgrades as $upgrade) {
            echo $upgrade . "\n";
            App::db()->query($upgrade);
        }

        App::walkModuleRootClasses('Installation', function($cl) {
            $cl->install();
        });
    }

}