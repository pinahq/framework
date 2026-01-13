<?php

namespace Pina\Data;

use Pina\TableDataGateway;

class QueryDataCollection extends DataCollection
{
    protected $query;

    public function __construct(TableDataGateway $query)
    {
        $this->query = $query;
    }

    protected function makeQuery()
    {
        return clone $this->query;
    }

    public function getSchema(): Schema
    {
        return $this->makeQuery()->getQuerySchema()->setInnerGroupStatic();
    }

    public function getListSchema(): Schema
    {
        return $this->makeQuery()->getQuerySchema()->forgetDetailed();
    }

}