<?php

namespace Pina\Types;

use Pina\Controls\FormControl;
use Pina\Data\Field;

class GigabyteType extends IntegerType
{

    public function makeControl(Field $field, $value): FormControl
    {
        if (!$field->isStatic()) {
            $value = $this->format($value ?? '');
        }

        return parent::makeControl($field, $value);
    }

    public function format($value): string
    {
        if ($value == 0) {
            return '0 GB';
        }
        return $this->convertBytesToString($value * 1024 * 1024 * 1024);
    }

    public function normalize($value, $isMandatory)
    {
        $value = $this->convertStringToBytes($value) / (1024 * 1024 * 1024);

        return parent::normalize($value, $isMandatory);
    }

    protected function convertBytesToString($value)
    {
        $precision = 2;
        $base = log($value, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

    protected function convertStringToBytes($from)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $number = trim(substr($from, 0, -2));
        $suffix = strtoupper(substr($from,-2));
        //B or no suffix
        if(is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $from);
        }
        $exponent = array_flip($units)[$suffix] ?? null;
        if($exponent === null) {
            return null;
        }
        return $number * (1024 ** $exponent);
    }

}