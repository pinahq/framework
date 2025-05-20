<?php

namespace Pina\Types;

use Pina\Html;

class TitleType extends StringType
{

    public function draw($value): string
    {
        return Html::tag('strong', $value);
    }

}