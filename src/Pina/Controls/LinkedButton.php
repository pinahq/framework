<?php

namespace Pina\Controls;

use Pina\Html;

class LinkedButton extends Button
{

    protected $link = '';

    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function draw()
    {
        return Html::a($this->title . $this->compile(), $this->link, $this->makeAttributes(['class' => 'btn btn-primary']));
    }

}
