<?php


namespace Pina\Types;


use Pina\App;
use Pina\Controls\FormControl;
use Pina\Data\Field;
use Pina\Controls\FormCheckbox;

class CheckedEnabledType extends EnabledType
{

    public function makeControl(Field $field, $value): FormControl
    {
        /** @var FormCheckbox $checkbox */
        $checkbox = App::make(FormCheckbox::class);
        $checkbox->setName($field->getKey());
        $checkbox->setValue('Y');
        $checkbox->setType('checkbox');
        $checkbox->setTitle($field->getTitle());
        if (!empty($value)) {
            $checkbox->setChecked($value);
        }

        return $checkbox;
    }

}