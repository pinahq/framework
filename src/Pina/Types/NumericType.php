<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormInput;
use Pina\Components\Field;
use function Pina\__;

class NumericType extends IntegerType
{

    public function makeControl(Field $field, $value)
    {
        return parent::makeControl($field, $value)->setType('text');
    }

    public function validate(&$value)
    {
        if (!is_numeric($value)) {
            return __("Укажите число");
        }
        
        if (empty($value)) {
            $value = 0;
        }

        return null;
    }

}
