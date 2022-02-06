<?php

namespace Pina\Types;

use Pina\Controls\FormInput;
use Pina\Controls\HiddenInput;
use Pina\App;
use Pina\Data\Field;

/**
 * @deprecated в пользу Field::setHidden()
 */
class HiddenType extends StringType
{

    public function makeControl(Field $field, $value)
    {
        $input = $this->makeInput();
        $input->setName($field->getKey());
        $input->setValue($value);

        return $input;
    }

    /**
     * @return FormInput
     */
    protected function makeInput()
    {
        return App::make(HiddenInput::class);
    }
}
