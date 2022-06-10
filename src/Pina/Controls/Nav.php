<?php


namespace Pina\Controls;

use Pina\Html;
use Pina\Url;

class Nav extends Control
{

    protected $items = [];

    protected $location = '';
    protected $current = '';

    public function setLocation(string $location)
    {
        $this->location = $location;
    }

    public function push($title, $link)
    {
        array_push($this->items, [$title, $link]);
        return $this;
    }

    public function pop()
    {
        return array_pop($this->items);
    }

    protected function draw()
    {
        return Html::tag(
            'ul',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        $this->resolveCurrent();//не очень хорошо, так как работает через изменение состояния, а не через отображение

        $c = '';
        foreach ($this->items as $item) {
            list($title, $link) = $item;
            $c .= $this->drawItem($title, $link);
        }
        return $c;
    }

    protected function drawItem($title, $link)
    {
        return Html::li(
            Html::a($title, $link, $this->makeLinkOptions($link)),
            $this->makeItemOptions($link)
        );
    }

    protected function makeLinkOptions($link)
    {
        $options = [];
        if ($this->isActive($link)) {
            $options['class'] = 'nav-link active';
        } else {
            $options['class'] = 'nav-link';
        }
        return $options;
    }

    protected function makeItemOptions($link)
    {
        $options = [];
        $options['class'] = 'nav-item';
        return $options;
    }

    protected function resolveCurrent()
    {
        $scores = [];
        foreach ($this->items as $k => $item) {
            list($title, $link) = $item;
            $scores[$k] = Url::nestedWeight($link, $this->location);
        }

        $max = $scores ? max($scores) : 0;
        if ($max == 0) {
            $this->current = '';
            return;
        }

        foreach ($scores as $k => $score) {
            if ($score == $max) {
                list($title, $link) = $this->items[$k];
                $this->current = $link;
                break;
            }
        }
    }

    protected function isActive($link)
    {
        return $this->current == $link;
    }

}
