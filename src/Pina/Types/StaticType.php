<?php

namespace Pina\Types;

use Pina\App;
use Pina\Components\Field;
use Pina\Controls\FormStatic;

class StaticType extends StringType
{

    public function makeControl(Field $field, $value)
    {
        /** @var FormStatic $input */
        $input = App::make(FormStatic::class);
        $star = $field->isMandatory() ? ' *' : '';
        $input->setTitle($field->getTitle() . $star);
        $input->setValue($value);
        return $input;
    }

}
