<?php

namespace Pina\Data;

use Pina\Container\AbstractCallbackContainer;

class SchemaExtension extends AbstractCallbackContainer
{

    public function has(string $id)
    {
        return true;
    }

    protected function resolve(string $id)
    {
        return new Schema();
    }

}