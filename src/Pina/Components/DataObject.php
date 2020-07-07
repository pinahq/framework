<?php

namespace Pina\Components;

abstract class DataObject
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

    abstract public function draw();

    public function append($obj)
    {
        $this->after[] = $obj;
        return $this;
    }

    public function prepend($obj)
    {
        $this->before[] = $obj;
        return $this;
    }

    public function wrap($obj)
    {
        $this->wrappers[] = $obj;
        return $this;
    }

    protected function process($inner)
    {
        $r = '';
        foreach ($this->before as $v) {
            $r .= $v->draw();
        }

        $r .= $inner;

        foreach ($this->after as $v) {
            $r .= $v->draw();
        }

        foreach ($this->wrappers as $v) {
            $r = $v->wrap($r, $this);
        }

        return $r;
    }

}
