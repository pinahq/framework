<?php

namespace Pina;

class CronEventGateway extends TableDataGateway
{

    protected static $table = "cron_event";
    protected static $fields = array(
        'id' => "varchar(64) NOT NULL DEFAULT ''",
        'event' => "VARCHAR(128) NOT NULL default ''",
        'data' => "longblob default NULL",
        'priority' => "int(10) NOT NULL default 0",
        'created' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
    );
    protected static $indexes = array(
        'PRIMARY KEY' => 'id',
        'KEY created' => 'created',
    );

    public function getTriggers()
    {
        return [
                [
                $this->getTable(),
                'before insert',
                "SET NEW.id=UUID();"
            ],
        ];
    }

}
