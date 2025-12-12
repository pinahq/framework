<?php

namespace Pina\Types;

use Pina\Access;
use Pina\App;
use Pina\Html;
use Pina\Http\Location;

abstract class LocationLinkedQueryDirectoryType extends QueryDirectoryType
{
    abstract protected function getLocation(): Location;

    public function play($value): string
    {
        $formatted = $this->draw($value);
        $location = $this->getLocation();

        if (!App::access()->isPermitted($location->resource('@'))) {
            return $formatted;
        }

        return Html::a($formatted, $location->link('@/:id', ['id' => $value]));
    }

}