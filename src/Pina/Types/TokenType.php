<?php

namespace Pina\Types;

class TokenType extends StringType
{

    public function getSize(): int
    {
        return 32;
    }

}