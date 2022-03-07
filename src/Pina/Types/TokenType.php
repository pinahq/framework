<?php

namespace Pina\Types;

class TokenType extends StringType
{

    public function getSize()
    {
        return 32;
    }

}