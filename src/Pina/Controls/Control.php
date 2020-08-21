<?php

namespace Pina\Controls;

class Control
{

    protected $isBuildStarted = false;
    protected $controls = [];
    protected $after = [];
    protected $before = [];
    protected $classes = [];

    /**
     * 
     * @return \static
     */
    public static function instance()
    {
        return new static();
    }

    public function startBuild()
    {
        $this->isBuildStarted = true;
    }

    public function isBuildStarted()
    {
        return $this->isBuildStarted;
    }

    public function append($control)
    {
        if ($this->isBuildStarted) {
            //пошла сборка контролов по запросу отрисовки
            $this->controls[] = $control;
        } else {
            //запоминаем, какие контролы хотят отрисоваться после основного блока
            $this->after[] = $control;
        }
    }

    public function prepend($control)
    {
        array_unshift($this->before, $control);
    }

    public function addClass($c)
    {
        $this->classes[] = $c;
    }

    protected function makeClass($additional)
    {
        $additionalArray = is_array($additional) ? $additional : [$additional];
        return implode(' ', array_merge($this->classes, $additionalArray));
    }

    public function compile()
    {
        $r = '';
        foreach ($this->before as $c) {
            $r .= $c->draw();
        }
        foreach ($this->controls as $c) {
            $r .= $c->draw();
        }
        foreach ($this->after as $c) {
            $r .= $c->draw();
        }
        return $r;
    }

    public function draw()
    {
        return '';
    }

}
