<?php


namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormTextarea;

class MultiLineStringType extends StringType
{
    protected function makeInput()
    {
        return App::make(FormTextarea::class);
    }

    public function format($value): string
    {
        return $value ?? '';
    }

    public function draw($value): string
    {
        return nl2br($value);
    }

}