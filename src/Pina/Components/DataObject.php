<?php

namespace Pina\Components;

abstract class DataObject
{

    /**
     * @var Schema
     */
    protected $schema = null;
    protected $meta = [];
    
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
        return Registry::get($alias)->basedOn($this);
    }
    
    public function meta($key, $value)
    {
        $this->meta[$key] = $value;
        return $this;
    }
    
    abstract function draw();

}
