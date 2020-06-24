<?php

namespace Pina\Components;

class DataObject
{

    /**
     * @var Schema
     */
    protected $schema = null;

    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
        return $this;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function turnTo($alias)
    {
        return \Pina\App::container()->get('component:' . $alias)->basedOn($this);
    }

}
