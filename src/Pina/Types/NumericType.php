<?php

namespace Pina\Types;

use Pina\Controls\FormControl;
use Pina\Data\Field;

use function Pina\__;

class NumericType extends IntegerType
{

    public function getSize(): int
    {
        return 12;
    }

    public function makeControl(Field $field, $value): FormControl
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

        $maxValue = pow(10, ($this->getSize() - $this->getDecimals()));
        if ($value >= $maxValue) {
            throw new ValidateException(__("Укажите число меньше") . ' ' . $maxValue);
        }

        return $value;
    }

    public function getSQLType(): string
    {
        return "decimal(" . $this->getSize() . "," . $this->getDecimals() . ")";
    }

    public function getDefault()
    {
        return '0.' . str_repeat('0', $this->getDecimals());
    }

    protected function getDecimals()
    {
        return 2;
    }

}
