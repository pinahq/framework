<?php

namespace Pina\Types;

use Pina\Data\Field;

use function Pina\__;

class NumericType extends IntegerType
{

    public function getSize()
    {
        return 12;
    }

    public function makeControl(Field $field, $value)
    {
        return parent::makeControl($field, $value)->setType('text');
    }

    public function normalize($value, $isMandatory)
    {
        if (!is_numeric($value)) {
            throw new ValidateException(__("Укажите число"));
        }

        if (empty($value)) {
            return 0;
        }

        return $value;
    }

    public function getSQLType()
    {
        $decimals = 2;
        return "decimal(" . $this->getSize() . "," . $decimals . ")";
    }

}
