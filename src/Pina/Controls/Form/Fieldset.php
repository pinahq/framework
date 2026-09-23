<?php

namespace Pina\Controls\Form;

use Pina\Controls\Components\Card;
use Pina\Html;

class Fieldset extends Card
{
    protected function draw(): string
    {
        $header = '';
        if ($this->title) {
            $header = Html::tag('legend', $this->title);
        }
        $content = $header . $this->drawContent();
        if (empty($content)) {
            return '';
        }
        return Html::tag(
            'fieldset',
                $content,
            $this->makeAttributes()
        );
    }
}