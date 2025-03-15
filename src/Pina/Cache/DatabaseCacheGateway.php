<?php

namespace Pina\Cache;

use Pina\Data\Schema;
use Pina\TableDataGateway;
use Pina\Types\BlobType;
use Pina\Types\IntegerType;
use Pina\Types\StringType;
use Pina\Types\TextType;
use Pina\Types\TimestampType;
use Pina\Types\TokenType;
use Pina\Types\UUIDType;

class DatabaseCacheGateway extends TableDataGateway
{

    protected static $table = "database_cache";

    /**
     * @return Schema
     * @throws \Exception
     */
    public function getSchema()
    {
        $schema = parent::getSchema();
        $schema->add('id', 'ID', TokenType::class)->setMandatory();
        $schema->setPrimaryKey('id');
        $schema->add('data', 'Data', TextType::class);
        $schema->add('ttl', 'Ttl', IntegerType::class);
        $schema->addCreatedAt('Created at');
        $schema->add('expired_at', 'Expired at', TimestampType::class)->setNullable();
        return $schema;
    }


    public function getTriggers()
    {
        $expiredAt = "SET NEW.expired_at=DATE_ADD(NOW(), INTERVAL NEW.ttl SECOND);";

        return [
            [
                $this->getTable(),
                'before insert',
                $expiredAt
            ],
            [
                $this->getTable(),
                'before update',
                $expiredAt
            ],
        ];
    }

    public function whereNotExpired()
    {
        return $this->where($this->getAlias().".expired_at >= NOW()");
    }

}