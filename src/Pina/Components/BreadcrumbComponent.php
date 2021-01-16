<?php

namespace Pina\Components;

class BreadcrumbComponent extends MenuComponent
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
        $count = count($data);
        foreach ($data as $idx => $line) {
            $title = $line[$keys[0]] ?? '';
            $link = ($idx < $count - 1) ? ($line[$keys[1]] ?? '') : null;

            $list->append($this->makeListItem($title, $link));
        }
        $this->append($list);
    }

    /**
     * Возвращает экземпляр списка-контейнера хлебных крошек
     * @return \Pina\Controls\UnorderedList
     */
    protected function makeList()
    {
        $list = $this->control(\Pina\Controls\UnorderedList::class);
        $list->addClass('breadcrumb');
        return $list;
    }

    /**
     * Возвращает элемент хлебных крошек
     * @param string $title
     * @param string $link
     * @return \Pina\Controls\ListItem
     */
    protected function makeListItem($title, $link)
    {
        $item = isset($link) ? $this->control(\Pina\Controls\LinkedListItem::class) : $this->control(\Pina\Controls\ListItem::class);

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
