<?php

namespace Pina\Place;

class PlaceContent
{

    protected $text = '';

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function __invoke()
    {
        return $this->text;
    }

}