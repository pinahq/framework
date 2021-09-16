<?php


namespace Pina\Types;


class TimestampType extends StringType
{
    public function getSize()
    {
        return 19;//strlen('0000-00-00 00:00:00')
    }

    public function getSQLType()
    {
        return 'timestamp';
    }

    public function isNullable()
    {
        return false;
    }

    public function getDefault()
    {
        return null;
    }
}