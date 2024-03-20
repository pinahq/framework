<?php


namespace Pina\Types;


use Pina\TableDataGateway;

//специальный тип для поискового поля
class SearchType extends StringType
{

    public function isSearchable(): bool
    {
        return false;
    }

    public function isFiltrable(): bool
    {
        return false;
    }

    public function filter(TableDataGateway $query, $key, $value): void
    {
    }

}