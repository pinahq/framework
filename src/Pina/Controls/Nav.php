<?php


namespace Pina\Controls;

use Pina\App;
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
            $this->makeAttributes(['class' => 'nav'])
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
        $options['class'] = $this->makeLinkClasses($item);
        if ($this->isActive($item)) {
            $options['class'] .= ' active';
        }
        if ($this->isExternalLink($item->getLink())) {
            $options['target'] = '_blank';
        }
        return $options;
    }

    protected function makeLinkClasses(LinkedItemInterface $item)
    {
        return 'nav-link';
    }

    protected function makeItemOptions(LinkedItemInterface $item)
    {
        $options = [];
        $options['class'] = $this->makeItemClasses($item);
        return $options;
    }

    protected function makeItemClasses(LinkedItemInterface $item)
    {
        return 'nav-item';
    }

    protected function isExternalLink(string $link)
    {
        $host = parse_url($link, PHP_URL_HOST);
        return $host != App::host();
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
