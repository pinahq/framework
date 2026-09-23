<?php

namespace Pina\Controls\Record;

use Pina\App;
use Pina\Controls\Components\Card;
use Pina\Controls\Form\Fieldset;

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