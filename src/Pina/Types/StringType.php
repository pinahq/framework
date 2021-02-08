<?php

namespace Pina\Types;

use Pina\Controls\FormInput;
use Pina\Components\Field;
use function Pina\__;

class StringType implements TypeInterface
{

    public function makeControl(Field $field, $value)
    {
        /** @var FormInput $input */
        $input = \Pina\App::make(FormInput::class);
        $input->setType('text');
        $input->setName($field->getKey());
        $input->setTitle($field->getTitle());
        $input->setValue($value);
        return $input;
    }

    public function getSize()
    {
        return 512;
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
        $size = $this->getSize();
        if (strlen($value) > $size) {
            return \sprintf(__("Укажите значение короче. Максимальная длина %s символов"), $size);
        }

        return null;
    }

}