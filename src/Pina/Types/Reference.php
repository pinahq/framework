<?php

namespace Pina\Types;

use Pina\App;
use Pina\TableDataGateway;

class Reference extends QueryDirectoryType
{
    /** @var TableDataGateway */
    protected $query;

    public function __construct(TableDataGateway $query)
    {
        $this->query = $query;
    }

    protected function makeQuery(): TableDataGateway
    {
        return clone $this->query;
    }

    public function isNullable(): bool
    {
        return true;
    }

    public function getSQLType(): string
    {
        $pk = $this->query->getSinglePrimaryKey();
        $fieldset = $this->query->getSchema()->fieldset([$pk])->makeSchema();
        foreach ($fieldset as $f) {
            return App::type($f->getType())->getSQLType();
        }

        throw new \Exception(get_class($this->query) . ' single PK has not been found');
    }
}