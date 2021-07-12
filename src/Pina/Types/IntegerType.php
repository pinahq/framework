<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormInput;
use Pina\Components\Field;

use function Pina\__;

class IntegerType implements TypeInterface
{

    public function setContext($context)
    {
        return $this;
    }

    public function makeControl(Field $field, $value)
    {
        $star = $field->isMandatory() ? ' *' : '';
        return App::make(FormInput::class)
            ->setName($field->getKey())
            ->setTitle($field->getTitle() . $star)
            ->setValue($value)
            ->setType('text');
    }

    public function format($value)
    {
        return $value;
    }

    public function getSize()
    {
        return 11;
    }

    public function getDefault()
    {
        return 0;
    }

    public function isNullable()
    {
        return false;
    }

    public function getVariants()
    {
        return [];
    }

    public function normalize($value, $isMandatory)
    {
        if (strval(intval($value)) != strval($value)) {
            throw new ValidateException(__("Укажите целое число"));
        }

        return intval($value);
    }

    public function getSQLType()
    {
        return "int(" . $this->getSize() . ")";
    }

}