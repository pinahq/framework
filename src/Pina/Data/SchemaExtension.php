<?php

namespace Pina\Data;

use Pina\Container\AbstractCallbackContainer;
use Pina\Container\SingletonTrait;

class SchemaExtension extends AbstractCallbackContainer
{
    use SingletonTrait;

    public function has(string $id)
    {
        return true;
    }

    protected function resolve(string $id)
    {
        return new Schema();
    }

}