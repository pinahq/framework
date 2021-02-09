<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormTextarea;
use Pina\Components\Field;

class TextType extends StringType
{

    public function makeControl(Field $field, $value)
    {
        /** @var FormTextarea $input */
        $input = App::make(FormTextarea::class);
        $input->setType('text');
        $input->setName($field->getKey());
        $star = $field->isMandatory() ? ' *' : '';
        $input->setTitle($field->getTitle() . $star);
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
