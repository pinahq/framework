<?php

namespace Pina\Controls\Record;

use Pina\App;
use Pina\Controls\Components\BodyLessCard;

class BodyLessRecordFormCompiler extends RecordFormCompiler
{

    protected function makeCard()
    {
        return App::make(BodyLessCard::class);
    }

}