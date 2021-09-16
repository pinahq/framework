<?php


namespace Pina\Types;


use Pina\Data\Field;

/**
 * @deprecated
 */
class NullableIntegerType extends IntegerType
{

    public function makeControl(Field $field, $value)
    {
        return parent::makeControl($field, is_null($value) ? '' : $value);
    }

    public function format($value)
    {
        return is_null($value) ? '-' : $value;
    }

    public function isNullable()
    {
        return true;
    }

    public function getDefault()
    {
        return null;
    }

    public function normalize($value, $isMandatory)
    {
        if ($value == '' && !$isMandatory) {
            return null;
        }

        return parent::normalize($value, $isMandatory);
    }

}