<?php

namespace Pina\SQL;

use Pina\Data\Schema;

interface DefinitionInterface
{
    public function getSource(): string;

    public function getSchema(): Schema;
}