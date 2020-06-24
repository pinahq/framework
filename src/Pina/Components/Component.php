<?php

namespace Pina\Components;

class Component
{

    protected $childs = [];

    public static function basedOn(Component $c)
    {
        
    }

    public function turnTo(Component $c)
    {
        
    }

    public function draw()
    {
        $r = '';
        foreach ($this->childs as $child) {
            $r .= $child->draw();
        }
        return $r;
    }

    public function append($child)
    {
        $this->childs[] = $child;
    }

}
