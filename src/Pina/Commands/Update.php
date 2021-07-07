<?php


namespace Pina\Commands;

use Pina\App;
use Pina\Command;

class Update extends Command
{

    protected function execute($input = '')
    {
        App::walkClasses('Installation', function($cl) {
            $cl->prepare();
        });

        $upgrades = App::getUpgrades();
        App::db()->batch($upgrades);

        App::walkClasses('Installation', function($cl) {
            $cl->install();
        });

        return join($upgrades, "\r\n");
    }

}