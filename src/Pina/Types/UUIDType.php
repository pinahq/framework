<?php


namespace Pina\Types;


class UUIDType extends StringType
{

    public function getSize()
    {
        return 36;
    }

}