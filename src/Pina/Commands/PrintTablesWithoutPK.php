<?php

namespace Pina\Commands;

use Pina\App;
use Pina\Command;
use Pina\TableDataGateway;

class PrintTablesWithoutPK extends Command
{

    protected function execute($input = '')
    {
        App::walkModuleClasses(
            'Gateway',
            function (TableDataGateway $gw) {
                $table = $gw->getTable();
                if (empty($table)) {
                    return;
                }
                $pk = $gw->getSchema()->getPrimaryKey();
                if (empty($pk)) {
                    echo $table . ': ' . get_class($gw) . "\n";
                }
            }
        );

    }
}