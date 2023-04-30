<?php


namespace Pina\Events\Cron;


use Pina\Data\Schema;
use Pina\TableDataGateway;
use Pina\Types\IntegerType;
use Pina\Types\UUIDType;

class CronEventWorkerGateway extends TableDataGateway
{

    protected static $table = "cron_event_worker";

    /**
     * @return Schema
     * @throws \Exception
     */
    public function getSchema()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', UUIDType::class)->setMandatory();
        $schema->setPrimaryKey('id');
        $schema->add('worker_id', 'Worker ID', IntegerType::class)->setNullable();
        return $schema;
    }

    public function insertFromEvents(int $workerId, CronEventGateway $query): int
    {
        $query->resetSelect();
        $query->select('id');
        $query->calculate($workerId, 'worker_id');

        $this->db->query("INSERT IGNORE INTO ".$this->getTable()." (id, worker_id) " . $query->make());

        return $this->db->affectedRows();
    }
}