<?php

namespace Pina\Components;

class DataObject
{

    /**
     * @var Schema
     */
    protected $schema = null;
    
    public static function instance()
    {
        return new static();
    }

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
        return \Pina\App::getComponent($alias)->basedOn($this);
    }

}
