<?php

namespace Pina\Controls;

use Pina\Html;
use Pina\Paging;
use Pina\App;

class PagingControl extends Control
{

    /** @var Paging */
    protected $paging = null;
    protected $linkContext = [];

    public function init(Paging $paging)
    {
        $this->paging = $paging;
        return $this;
    }

    public function setLinkContext($context)
    {
        $this->linkContext = $context;
        return $this;
    }

    protected function draw()
    {
        $items = [];

        $current = $this->paging->getCurrent();
        $total = $this->paging->getPagesCount();
        
        if ($total <= 1) {
            return '';
        }

        for ($i = 1; $i <= $this->paging->getPagesCount(); $i ++) {

            $digits = ($i <= $current && $i >= $current - 1)
                || ($i >= $current && $i <= $current + 1)
                || ($i <= 5 &&  $current <= 4)
                || ($i >= $total - 4 &&  $current >= $total - 3)
                || $i == 1
                || $i == $total;

            $dotted = ($i < $current && $i == 2) || ($i > $current && $i == $total - 1);

            if ($digits) {
                $items [] = Html::tag('li', Html::a($i, App::link(App::resource(), array_merge($this->linkContext, ['page' => $i])), ['class' => 'page-link']), ['class' => 'page-item' . ($i == $current ? ' active' : '')]);
            } elseif ($dotted) {
                $items [] = Html::tag('li', Html::a('...', '', ['class' => 'page-link']), ['class' => 'page-item disabled']);
            }
        }

        return Html::tag('ul', implode('', $items), ['class' => 'pagination']);
    }

}
