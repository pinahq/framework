<?php


namespace Pina\Types;

class UnsignedIntegerType extends IntegerType
{
    public function getSQLType()
    {
        return "int(" . $this->getSize() . ") unsigned ";
    }
}