<?php

namespace Pina\Controls\Form;

use Pina\App;
use Pina\Controls\Components\BodyLessCard;
use Pina\Controls\Record\RecordFormCompiler;

class BodyLessRecordFormCompiler extends RecordFormCompiler
{

    protected function makeCard()
    {
        return App::make(BodyLessCard::class);
    }

}