<?php

namespace Pina\Components;

class DataObject extends \Pina\Controls\Control
{

    /**
     * @var Schema
     */
    protected $schema = null;
    protected $meta = [];
    protected $after = [];
    protected $before = [];
    protected $wrappers = [];

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

    public function forgetField($column)
    {
        $this->schema->forgetField($column);
        return $this;
    }

    public function turnTo($alias)
    {
        return Registry::get($alias)->basedOn($this);
    }

    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
        return $this;
    }

    public function getMeta($key)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }
    
    public function draw()
    {
        $this->build();
        return $this->compile();
    }

}
