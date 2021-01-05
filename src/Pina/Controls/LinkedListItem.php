<?php

namespace Pina\Controls;

use Pina\Html;

class LinkedListItem extends ListItem
{
    protected $link = '';
    protected $linkClass = null;

    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }
    
    public function setLinkClass($class)
    {
        $this->linkClass = $class;
        return $this;
    }

    public function draw()
    {
        return Html::tag('li', Html::a($this->text . $this->compile(), $this->link, ['class' => $this->linkClass]), ['class' => $this->makeClass()]);
    }

}
