<?php

namespace Pina\Controls;

use Pina\App;

class FieldsetRecordFormCompiler extends RecordFormCompiler
{
    /**
     * @return Card
     */
    protected function makeCard()
    {
        return App::make(Fieldset::class);
    }
}