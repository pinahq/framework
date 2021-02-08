<?php

namespace Pina\Types;

use Pina\Controls\FormInput;
use Pina\Components\Field;
use function Pina\__;

class IntegerType implements TypeInterface
{

    public function makeControl(Field $field, $value)
    {
        return \Pina\App::make(FormInput::class)
            ->setName($field->getKey())
            ->setTitle($field->getTitle())
            ->setValue($value)
            ->setType('number');
    }

    public function getSize()
    {
        return 11;
    }

    public function getDefault()
    {
        return 0;
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
        if (!is_integer($value)) {
            return __("Укажите целое число");
        }
        
        return null;
    }

}
