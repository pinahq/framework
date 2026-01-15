<?php

namespace Pina\Controls;

use Pina\Html;

class Fieldset extends Card
{
    protected function draw()
    {
        $header = '';
        if ($this->title) {
            $header = Html::tag('legend', $this->title);
        }
        $inner = $header . $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
        if (empty($inner)) {
            return '';
        }
        return Html::tag(
            'fieldset',
                $inner,
            $this->makeAttributes()
        );
    }
}