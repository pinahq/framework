<?php

namespace Pina\Controls;

use Pina\Html;

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
        return \json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

}
