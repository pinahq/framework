<?php

namespace Pina\Types;

use Pina\Controls\HiddenInput;
use Pina\App;
use Pina\Components\Field;

class HiddenType extends StringType
{

    public function makeControl(Field $field, $value)
    {
        /** @var FormInput $input */
        $input = App::make(HiddenInput::class);
        $input->setName($field->getKey());
        $input->setValue($value);

        return $input;
    }
}
