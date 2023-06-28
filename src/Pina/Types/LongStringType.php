<?php

namespace Pina\Types;

class LongStringType extends StringType
{

    public function getSize(): int
    {
        return 1000;
    }

}