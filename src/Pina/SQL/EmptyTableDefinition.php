<?php

namespace Pina\SQL;

use Pina\Data\Schema;

class EmptyTableDefinition implements DefinitionInterface
{

    protected $table = '';

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function getSource(): string
    {
        return '`' . $this->table . '`';
    }

    public function getSchema(): Schema
    {
        return new Schema();
    }
}