<?php

namespace Pina\Components;

use Pina\Controls\LinkedListItem;
use Pina\Controls\ListItem;
use Pina\Controls\UnorderedList;

class BreadcrumbComponent extends MenuComponent
{

    public function build()
    {
        $list = $this->makeList();
        $keys = $this->schema->getKeys();
        $data = $this->getTextData();
        $count = count($data);
        foreach ($data as $idx => $line) {
            $title = isset($line[$keys[0]]) ? $line[$keys[0]] : '';
            $link = ($idx < $count - 1) ? (isset($line[$keys[1]]) ? $line[$keys[1]] : '') : null;

            $list->append($this->makeListItem($title, $link));
        }
        $this->append($list);
    }

    /**
     * Возвращает экземпляр списка-контейнера хлебных крошек
     * @return UnorderedList
     */
    protected function makeList()
    {
        $list = $this->control(UnorderedList::class);
        $list->addClass('breadcrumb');
        return $list;
    }

    /**
     * Возвращает элемент хлебных крошек
     * @param string $title
     * @param string $link
     * @return ListItem
     */
    protected function makeListItem($title, $link)
    {
        $item = isset($link) ? $this->control(LinkedListItem::class) : $this->control(
            ListItem::class
        );

        $item->setText($title);
        $item->addClass('breadcrumb-item');
        if ($link) {
            $item->setLink($link);
        } else {
            $item->addClass('active');
        }
        return $item;
    }

}
