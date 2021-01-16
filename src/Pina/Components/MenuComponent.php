<?php

namespace Pina\Components;

use Pina\Components\ListData;
use Pina\Components\Schema;
use function Pina\__;

class MenuComponent extends ListData //implements ComponentInterface
{

    public function __construct()
    {
        $this->schema = $this->getDefaultSchema();
    }

    public function build()
    {
        $list = $this->makeList();
        $keys = $this->schema->getKeys();
        $data = $this->getData();
        foreach ($data as $idx => $line) {
            $title = $line[$keys[0]] ?? '';
            $link = $line[$keys[1]] ?? '';
            $list->append($this->makeListItem($title, $link));
        }
        $this->append($list);
    }

    protected function makeList()
    {
        $list = $this->control(\Pina\Controls\UnorderedList::class);
        $list->addClass('nav');
        return $list;
    }

    protected function makeListItem($title, $link)
    {
        $item = isset($link) ? $this->control(\Pina\Controls\LinkedListItem::class)->setLinkClass('nav-link') : $this->control(\Pina\Controls\ListItem::class);

        $item->setText($title);
        $item->addClass('nav-item');
        if ($link) {
            $item->setLink($link);
        }
        return $item;
    }

    protected function getDefaultSchema()
    {
        $schema = new Schema;
        $schema->add('title', __('Title'), 'string');
        $schema->add('link', __('Link'), 'string');
        return $schema;
    }

}
