<?php


namespace Pina\Controls\Components;

use Pina\Controls\Control;
use Pina\Html;

class Checkbox extends Control
{

    protected $id;
    protected $name;
    protected $value;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    protected function draw(): string
    {
        $input = Html::input('checkbox', $this->name, $this->value, $this->makeAttributes(['id' => $this->id]));
        $label = Html::label('', $this->id);
        return $input . $label;
    }

}