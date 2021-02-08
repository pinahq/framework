<?php

namespace Pina\Types;

use Pina\Controls\FormTextarea;
use Pina\Components\Field;

class TextType extends StringType
{

    public function makeControl(Field $field, $value)
    {
        /** @var FormInput $input */
        $input = \Pina\App::make(FormTextarea::class);
        $input->setType('text');
        $input->setName($field->getKey());
        $input->setTitle($field->getTitle());
        $input->setValue($value);
        return $input;
    }

    public function getSize()
    {
        return 1024 * 1024;
    }

    public function isNullable()
    {
        return true;
    }

}
