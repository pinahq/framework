<?php

namespace Pina\Controls;

class Control
{

    protected $isBuildStarted = false;
    protected $controls = [];
    protected $after = [];
    protected $before = [];
    protected $classes = [];
    protected $layout = null;
    protected $dataAttributes = [];

    /**
     * 
     * @return \static
     */
    public static function instance()
    {
        return new static();
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    public function getLayout()
    {
        return $this->layout;
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
        return $this;
    }

    public function prepend($control)
    {
        array_unshift($this->before, $control);
        return $this;
    }

    public function addClass($c)
    {
        $this->classes[] = $c;
        return $this;
    }

    protected function makeClass($additional = null)
    {
        $classes = $this->classes;
        if (!is_null($additional)) {
            $classes = array_merge($classes, is_array($additional) ? $additional : [$additional]);
        }
        return count($classes) > 0 ? implode(' ', $classes) : null;
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

    public function __toString()
    {
        return $this->draw();
    }

    protected function makeAttributes($attributes = [])
    {
        $class = !empty($attributes['class']) ? $attributes['class'] : null;
        $base = \array_merge(['class' => $this->makeClass($class)], $this->getDataAttributes());
        unset($attributes['class']);

        return array_merge($base, $attributes);
    }

    public function setDataAttribute($key, $value)
    {
        $this->dataAttributes['data-' . $key] = $value;
        return $this;
    }

    public function getDataAttributes()
    {
        return $this->dataAttributes;
    }

}
