<?php


namespace Pina\Types;

class UnsignedIntegerType extends IntegerType
{
    public function getSQLType(): string
    {
        return "int(" . $this->getSize() . ") unsigned ";
    }
}