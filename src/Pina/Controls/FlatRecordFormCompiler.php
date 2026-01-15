<?php

namespace Pina\Controls;

use Pina\App;

class FlatRecordFormCompiler extends RecordFormCompiler
{
    protected function makeCard()
    {
        return App::make(BodyLessCard::class);
    }
}