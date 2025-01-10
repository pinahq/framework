<?php

namespace Pina\Controls;

use Pina\App;

class BodyLessRecordFormCompiler extends RecordFormCompiler
{

    protected function makeCard()
    {
        return App::make(BodyLessCard::class);
    }

}