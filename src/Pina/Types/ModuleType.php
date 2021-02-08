<?php

namespace Pina\Types;

use Pina\Components\SelectComponent;
use Pina\App;
use Pina\Components\Field;

class ModuleType implements TypeInterface
{
    
    protected $resource = null;
    
    public function setResource($resource)
    {
        $this->resource = $resource;
    }
    
    public function makeControl(Field $field, $value)
    {
        $input = App::make(SelectComponent::class);
        $input->basedOn(App::router()->run($this->resource, 'get'));
        $input->setName($field->getKey());
        $input->setTitle($field->getTitle());
        $input->setValue($value);
        return $input;
    }

    public function getSize()
    {
        return null;
    }

    public function getDefault()
    {
        return '';
    }

    public function isNullable()
    {
        return false;
    }

    public function getVariants()
    {
        return [];
    }

    public function validate(&$value)
    {
        return null;
    }

}