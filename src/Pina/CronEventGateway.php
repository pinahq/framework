<?php

namespace Pina;

use Pina\Components\Schema;

class CronEventGateway extends TableDataGateway
{

    protected static $table = "cron_event";
    protected static $fields = array();
    protected static $indexes = array(
        'PRIMARY KEY' => 'id',
        'KEY created' => 'created',
    );

    public function getSchema()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', 'uuid', true, '');
        $schema->add('event', 'Event', 'string', true, '');
        $schema->add('data', 'Data', 'blob', false, null);
        $schema->add('priority', 'Priority', 'int', false, 0);
        $schema->add('created', 'Created at', 'timestamp', true, 'CURRENT_TIMESTAMP');
        $schema->setPrimaryKey('id');
        $schema->addKey('created');
        return $schema;
    }

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
