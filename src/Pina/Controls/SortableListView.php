<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Controls\Interfaces\LinkedListItemInterface;
use Pina\Html;

class SortableListView extends UnorderedList
{
    /**
     * @var LinkedListItemInterface[]
     */
    protected $data;

    /**
     * @param LinkedListItemInterface[] $data
     */
    public function load($data)
    {
        $this->data = $data;
        return $this;
    }

    public function __construct()
    {
        $this
            ->addClass('feeds')
            ->addClass('ui-sortable')
            ->addClass('pina-sortable');
    }

    public function setHandler($resource, $method, $params)
    {
        $this->setDataAttribute('method', $method);
        $this->setDataAttribute('resource', ltrim($resource, '/'));
        $this->setDataAttribute('params', htmlspecialchars(http_build_query($params), ENT_COMPAT));
        return $this;
    }

    protected function compile()
    {
        $content = '';
        foreach ($this->data as $item) {
            $content .= $this->makeListItem($item);
        }
        return $content . parent::compile();
    }

    /**
     * @param LinkedListItemInterface $item
     * @return ListItem
     */
    protected function makeListItem($item)
    {
        /** @var ListItem $li */
        $li = App::make(ListItem::class);
        $li->addClass('draggable')->addClass('ui-sortable-handle');
        $li->addClass($item->getHtmlClass());
        $li->setDataAttribute('id', $item->getId());

        $subtitle = $item->getText();
        $muted = '';
        if (!empty($subtitle)) {
            $muted = Html::tag('span', $subtitle, ['class' => 'text-muted']);
        }
        $li->setText($item->getIconHtml() . $muted . Html::a($item->getTitle(), $item->getLink()));
        return $li;
    }


}