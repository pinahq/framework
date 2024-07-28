<?php

namespace Pina\Commands;

use Pina\App;
use Pina\Command;
use Pina\TableDataGateway;

class PrintUnusedTables extends Command
{

    protected function execute($input = '')
    {
        $pattern = $input ? $input : '%s';

        $usedTables = [];

        App::walkModuleClasses(
            'Gateway',
            function (TableDataGateway $gw) use (&$usedTables) {
                $table = $gw->getTable();
                if (empty($table)) {
                    return;
                }

                $usedTables[] = $table;
            }
        );

        $allTables = App::db()->col("SHOW TABLES");
        if (!empty($allTables)) {
            $unusedTables = array_values(array_diff($allTables, $usedTables));
        } else {
            $unusedTables = [];
        }

        foreach ($unusedTables as $table) {
            echo sprintf($pattern, $table) . "\n";
        }
    }

}