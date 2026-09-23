<?php

namespace Pina\Data;

interface SchemaProviderInterface
{

    public function getSchema(): Schema;

}