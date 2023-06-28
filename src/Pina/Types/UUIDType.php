<?php


namespace Pina\Types;


class UUIDType extends StringType
{

    public function getSize(): int
    {
        return 36;
    }

}