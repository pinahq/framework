<?php

namespace Pina\Controls;

use Pina\Html;

class Card extends Control
{

    protected $title = '';

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function draw()
    {
        $header = '';
        if ($this->title) {
            $header = Html::tag('h5', $this->title, ['class' => 'card-title']);
        }
        return Html::tag('div', Html::tag('div', $header . $this->compile(), ['class' => 'card-body']), ['class' => $this->makeClass('card')]);
    }

}
