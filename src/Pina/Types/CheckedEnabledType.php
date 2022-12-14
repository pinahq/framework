<?php


namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormFlagStatic;
use Pina\Controls\FormControl;
use Pina\Controls\FormStatic;
use Pina\Data\Field;
use Pina\Controls\FormCheckbox;

class CheckedEnabledType extends EnabledType
{

    public function makeControl(Field $field, $value): FormControl
    {
        $control = $field->isStatic()
            ? $this->makeStatic()->setValue($value)
            : (
            $field->isHidden()
                ? $this->makeHidden()->setValue($value)
                : $this->makeCheckbox()->setValue('Y')->setChecked($value == 'Y')
            );


        $control->setName($field->getKey());
        $control->setTitle($field->getTitle());

        return $control;
    }

    protected function makeCheckbox(): FormCheckbox
    {
        /** @var FormCheckbox $checkbox */
        return App::make(FormCheckbox::class);
    }

    /**
     * @return FormStatic
     */
    protected function makeStatic()
    {
        return App::make(FormFlagStatic::class);
    }

}