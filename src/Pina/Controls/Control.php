<?php

namespace Pina\Controls;

class Control
{

    protected $controls = [];

    /**
     * 
     * @return \static
     */
    public static function instance()
    {
        return new static();
    }
    
    public function append($control)
    {
        $this->controls[] = $control;
    }
    
    public function prepend($control)
    {
        array_unshift($this->controls, $control);
    }
    
    public function compile()
    {
        $r = '';
        foreach ($this->controls as $c) {
            $r .= $c->draw();
        }
        return $r;
    }

    public function draw()
    {
        return '';
    }
    
} 