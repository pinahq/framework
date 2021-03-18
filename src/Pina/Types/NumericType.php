<?php

namespace Pina\Types;

use Pina\Components\Field;
use function Pina\__;

class NumericType extends IntegerType
{

    public function makeControl(Field $field, $value)
    {
        return parent::makeControl($field, $value)->setType('text');
    }

    public function normalize($value)
    {
        if (!is_numeric($value)) {
            throw new ValidateException( __("Укажите число"));
        }
        
        if (empty($value)) {
            return 0;
        }

        return $value;
    }

}
