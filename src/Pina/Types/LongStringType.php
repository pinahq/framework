<?php

namespace Pina\Types;

class LongStringType extends StringType
{

    public function getSize()
    {
        return 1000;
    }

}