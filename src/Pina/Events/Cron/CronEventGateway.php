<?php

namespace Pina\Events\Cron;

use Pina\Data\Schema;
use Pina\TableDataGateway;
use Pina\Types\BlobType;
use Pina\Types\IntegerType;
use Pina\Types\StringType;
use Pina\Types\TimestampType;
use Pina\Types\UUIDType;

class CronEventGateway extends TableDataGateway
{

    protected static $table = "cron_event";

    /**
     * @return Schema
     * @throws \Exception
     */
    public function getSchema()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', UUIDType::class)->setMandatory();
        $schema->setPrimaryKey('id');
        $schema->add('event', 'Event', StringType::class)->setMandatory();
        $schema->add('data', 'Data', BlobType::class);
        $schema->add('priority', 'Priority', IntegerType::class);
        $schema->add('delay', 'Delay', IntegerType::class);
        $schema->add('worker_id', 'Worker ID', IntegerType::class)->setNullable();
        $schema->addCreatedAt('Created at');
        $schema->add('scheduled_at', 'Scheduled at', TimestampType::class)->setNullable();
        $schema->add('started_at', 'Started at', TimestampType::class)->setNullable();
        $schema->addKey(['priority', 'scheduled_at']);
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
