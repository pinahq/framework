<?php

namespace Pina\Components;

use Pina\Controls\LinkedListItem;
use Pina\Controls\ListItem;

use Pina\Controls\UnorderedList;

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
            $title = isset($line[$keys[0]]) ? $line[$keys[0]] : '';
            $link = isset($line[$keys[1]]) ? $line[$keys[1]] : '';
            $list->append($this->makeListItem($title, $link));
        }
        $this->append($list);
    }

    protected function makeList()
    {
        $list = $this->control(UnorderedList::class);
        $list->addClass('nav');
        return $list;
    }

    protected function makeListItem($title, $link)
    {
        $item = isset($link) ? $this->control(LinkedListItem::class)->setLinkClass(
            'nav-link'
        ) : $this->control(ListItem::class);

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
