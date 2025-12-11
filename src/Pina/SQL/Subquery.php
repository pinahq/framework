<?php

namespace Pina\SQL;

use Pina\Data\Schema;
use Pina\SQL;

class Subquery implements DefinitionInterface
{

    protected $query;

    public function __construct(SQL $query)
    {
        $this->query = $query;
    }


    public function getSource(): string
    {
        return '(' . $this->query . ')';
    }

    public function getSchema(): Schema
    {
        return new Schema();
    }
}