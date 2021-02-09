<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormInput;
use Pina\Components\Field;
use function Pina\__;
use function sprintf;

class StringType implements TypeInterface
{

    public function setContext($context)
    {
        return $this;
    }

    public function makeControl(Field $field, $value)
    {
        /** @var FormInput $input */
        $input = App::make(FormInput::class);
        $input->setType('text');
        $input->setName($field->getKey());
        $star = $field->isMandatory() ? ' *' : '';
        $input->setTitle($field->getTitle() . $star);
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
        $value = trim($value);
        if (empty($value) && $this->isNullable()) {
            $value = null;
            return null;
        }

        $size = $this->getSize();
        if (strlen($value) > $size) {
            return sprintf(__("Укажите значение короче. Максимальная длина %s символов"), $size);
        }

        return null;
    }

}
