<?php

namespace Pina\Controls;

class Json extends Control
{

    protected $data = '';

    public function setData(&$data)
    {
        $this->data = $data;
        return $this;
    }

    protected function draw()
    {
        return $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
    }

    protected function drawInner()
    {
        return \json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}
