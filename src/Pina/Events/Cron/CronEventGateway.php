<?php

namespace Pina\Events\Cron;

use Pina\Data\Schema;
use Pina\TableDataGateway;

class CronEventGateway extends TableDataGateway
{

    protected static $table = "cron_event";
    protected static $fields = array();
    protected static $indexes = array(
        'PRIMARY KEY' => 'id',
        'KEY queue' => ['priority', 'scheduled_at'],
    );

    public function getSchema()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', 'uuid')->setMandatory();
        $schema->add('event', 'Event', 'string')->setMandatory();
        $schema->add('data', 'Data', 'blob');
        $schema->add('priority', 'Priority', 'int');
        $schema->add('delay', 'Delay', 'int');
        $schema->add('worker_id', 'Worker ID', 'int')->setNullable();
        $schema->add('created_at', 'Created at', 'timestamp')->setDefault('CURRENT_TIMESTAMP');
        $schema->add('scheduled_at', 'Scheduled at', 'timestamp')->setNullable();
        $schema->add('started_at', 'Started at', 'timestamp')->setNullable();
        $schema->setPrimaryKey('id');
        $schema->addKey('created_at');
        return $schema;
    }

    public function getTriggers()
    {
        return [
            [
                $this->getTable(),
                'before insert',
                "SET NEW.id=UUID(), NEW.scheduled_at=NEW.created_at+NEW.delay;"
            ],
        ];
    }

    public function whereScheduled()
    {
        return $this->where($this->getAlias() . '.scheduled_at < NOW()');
    }

    public function start()
    {
        return $this->updateOperation($this->getAlias() . '.started_at=NOW()');
    }

    public function pushOff($delay)
    {
        $delay = intval($delay);
        $alias = $this->getAlias();
        return $this->updateOperation(
            "$alias.worker_id=NULL,"
            . "$alias.scheduled_at=NOW() + INTERVAL $alias.delay SECOND + INTERVAL $delay SECOND,"
            . "$alias.delay=$alias.delay + $delay"
        );
    }

}
