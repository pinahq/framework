<?php

namespace Pina\Controls\Components;

use Pina\Html;

/**
 * Элемент списка в виде ссылки
 */
class LinkedListItem extends ListItem
{
    protected $link = '';
    protected $linkClass = null;

    /**
     * @param string $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setLinkClass($class)
    {
        $this->linkClass = $class;
        return $this;
    }

    protected function draw(): string
    {
        return Html::tag(
            'li',
            Html::a(
                $this->text,
                $this->link,
                ['class' => $this->linkClass]
            ),
            $this->makeAttributes()
        );
    }

}
