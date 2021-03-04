<?php

namespace Pina\Components;

use Pina\Controls\LinkedListItem;
use Pina\Controls\ListItem;

use Pina\Controls\UnorderedList;

use function Pina\__;

class MenuComponent extends ListData //implements ComponentInterface
{

    protected $location = '';

    public function __construct()
    {
        $this->schema = $this->getDefaultSchema();
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function build()
    {
        $list = $this->makeList();
        $keys = $this->schema->getKeys();
        $data = $this->getTextData();
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
        foreach ($this->classes as $cl) {
            $list->addClass($cl);
        }
        return $list;
    }

    protected function makeListItem($title, $link)
    {
        $isActive = strncmp($this->location, $link, max(strlen($this->location), strlen($link))) == 0;
        $item = isset($link) ? $this->control(LinkedListItem::class)->setLinkClass(
            'nav-link'.($isActive ? ' active' : '')
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
