<?php


namespace Pina\Types;


class TimestampType extends StringType
{
    public function getSize(): int
    {
        return 19;//strlen('0000-00-00 00:00:00')
    }

    public function getSQLType(): string
    {
        return 'timestamp';
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function getDefault()
    {
        return null;
    }
}