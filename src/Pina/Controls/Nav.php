<?php


namespace Pina\Controls;

use Pina\Html;
use Pina\Model\LinkedItemInterface;
use Pina\Url;

class Nav extends Control
{

    /** @var LinkedItemInterface[] */
    protected $items = [];

    protected $location = '';

    public function setLocation(string $location)
    {
        $this->location = $location;
    }

    public function add(LinkedItemInterface $item)
    {
        array_push($this->items, $item);
        return $this;
    }

    public function addToHead(LinkedItemInterface $item)
    {
        array_unshift($this->items, $item);
        return $this;
    }

    public function count(): int
    {
        return count($this->items);
    }

    protected function draw()
    {
        $inner = $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
        if (empty($inner)) {
            return '';
        }

        return Html::tag(
            'ul',
            $inner,
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        $c = '';
        foreach ($this->items as $item) {
            $c .= $this->drawItem($item);
        }
        return $c;
    }

    protected function drawItem(LinkedItemInterface $item)
    {
        return Html::li(
            Html::a($item->getTitle(), $item->getLink(), $this->makeLinkOptions($item)),
            $this->makeItemOptions($item)
        );
    }

    protected function makeLinkOptions(LinkedItemInterface $item)
    {
        $options = [];
        if ($this->isActive($item)) {
            $options['class'] = 'nav-link active';
        } else {
            $options['class'] = 'nav-link';
        }
        return $options;
    }

    protected function makeItemOptions(LinkedItemInterface $item)
    {
        $options = [];
        $options['class'] = 'nav-item';
        return $options;
    }

    protected function calculateCurrent(): string
    {
        $scores = [];
        foreach ($this->items as $k => $item) {
            $scores[$k] = Url::nestedWeight($item->getLink(), $this->location);
        }

        $max = $scores ? max($scores) : 0;
        if ($max == 0) {
            return '';
        }

        foreach ($scores as $k => $score) {
            if ($score == $max) {
                return $this->items[$k]->getLink();
            }
        }

        return '';
    }

    protected function isActive(LinkedItemInterface $item)
    {
        return $this->calculateCurrent() == $item->getLink();
    }

}
