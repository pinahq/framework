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

    protected function resolveInput(Field $field): FormControl
    {
        if ($field->isStatic() && $field->isHidden()) {
            return $this->makeNoInput();
        }

        if ($field->isStatic()) {
            return $this->makeStatic();
        }

        if ($field->isHidden()) {
            return $this->makeHidden();
        }

        return $this->makeCheckbox();
    }

    public function normalize($value, $isMandatory)
    {
        $value = $value == 'Y' ? 'Y' : 'N';
        return parent::normalize($value, $isMandatory);
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